/**
 * @license xrayquire 0.0.0 Copyright jQuery Foundation and other contributors.
 * Released under MIT license, http://github.com/requirejs/xrayquire/LICENSE
 */
/*jslint nomen: true */
/*global requirejs, console, window */

/**
 * Put a script tag in the HTML that references this script right after the
 * script tag for require.js.
 */

var xrayquire;
(function () {
    'use strict';

    var contexts = {},
        config = typeof xrayquire === 'undefined' ? {} : xrayquire,
        s = requirejs.s,
        oldNewContext = s.newContext,
        tokenRegExp = /\{(\w+)\}/g,
        standardDeps = {
            require: true,
            exports: true,
            module: true
        },
        prop;

    function each(ary, func) {
        if (ary) {
            var i;
            for (i = 0; i < ary.length; i += 1) {
                if (ary[i] && func(ary[i], i, ary)) {
                    break;
                }
            }
        }
    }

    /**
     * Cycles over properties in an object and calls a function for each
     * property value. If the function returns a truthy value, then the
     * iteration is stopped.
     */
    function eachProp(obj, func) {
        var prop;
        for (prop in obj) {
            if (obj.hasOwnProperty(prop)) {
                if (func(obj[prop], prop)) {
                    break;
                }
            }
        }
    }

    function hasProp(obj, prop) {
        return obj.hasOwnProperty(prop);
    }

    /**
     * Simple function to mix in properties from source into target,
     * but only if target does not already have a property of the same name.
     * This is not robust in IE for transferring methods that match
     * Object.prototype names, but the uses of mixin here seem unlikely to
     * trigger a problem related to that.
     */
    function mixin(target, source, force, deepStringMixin) {
        if (source) {
            eachProp(source, function (value, prop) {
                if (force || !hasProp(target, prop)) {
                    if (deepStringMixin && typeof value !== 'string') {
                        if (!target[prop]) {
                            target[prop] = {};
                        }
                        mixin(target[prop], value, force, deepStringMixin);
                    } else {
                        target[prop] = value;
                    }
                }
            });
        }
        return target;
    }

    function getX(context) {
        if (!context.xray) {
            context.xray = {
                traced: {},
                traceOrder: [],
                mixedCases: {}
            };
        }
        return context.xray;
    }

    function modContext(context) {
        var oldLoad = context.load,
            modProto = context.Module.prototype,
            oldModuleEnable = modProto.enable,
            xray = getX(context),
            traced = xray.traced,
            mixedCases = xray.mixedCases;

        function trackModule(mod) {
            var id = mod.map.id,
                traceData;

            //If an intermediate module from a plugin, do not
            //track it
            if (mod.map.prefix && id.indexOf('_unnormalized') !== -1) {
                return;
            }

            //Cycle through the dependencies now, wire this up here
            //instead of context.load so that we get a recording of
            //modules as they are encountered, and not as they
            //are fetched/loaded, since things could fall over between
            //now and then.
            if (!traced[id] || !traced[id].deps || !traced[id].deps.length) {
                each(mod.depMaps, function (dep) {
                    var depId = dep.id,
                        lowerId = depId.toLowerCase();

                    if (mixedCases[lowerId] && depId !== mixedCases[lowerId].id) {
                        console.error('Mixed case modules may conflict: ' +
                                        formatId(mixedCases[lowerId].refId) +
                                        ' asked for: "' +
                                        mixedCases[lowerId].id +
                                        '" and ' +
                                        formatId(id) +
                                        ' asked for: "' +
                                        depId +
                                        '"');
                    } else {
                        mixedCases[lowerId] = {
                            refId: id,
                            id: depId
                        };
                    }
                });

                traceData = {
                    map: mod.map,
                    deps: mod.depMaps
                };

                //Only add this to the order if not previously added.
                if (!traced[id]) {
                    xray.traceOrder.push(id);
                }

                //Set the data again in case this enable has the
                //real dependencies. Some first calls of enable do
                //not have the dependencies known yet.
                traced[id] = traceData;
            }
        }

        modProto.enable = function () {
            var result = oldModuleEnable.apply(this, arguments);
            trackModule(this);
            return result;
        };

        //Collect any modules that are already in process
        eachProp(context.registry, function (mod) {
            if (mod.enabled) {
                trackModule(mod);
            }
        });

        return context;
    }

    //Mod any existing contexts.
    eachProp(requirejs.s.contexts, function (context) {
        modContext(context);
    });

    // //Apply mods to any new context.
    // s.newContext = function (name) {
    //     return modContext(oldNewContext)(name);
    // };

    /**
     * Public API
     */
    xrayquire = {
        // content removed intentionally
    };
}());
