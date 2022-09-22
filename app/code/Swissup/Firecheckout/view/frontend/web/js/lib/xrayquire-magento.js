/*global requirejs, console, xrayquire */

/**
 * Put a script tag in the HTML that references this script right after the
 * script tag for xrequire.js.
 */

(function () {
    'use strict';

    /**
     * Each implementation
     * @param {Mixed} ary
     * @param {Function} func
     */
    function each(ary, func) {
        var i;

        if (!ary) {
            return;
        }

        for (i = 0; i < ary.length; i += 1) {
            if (ary[i] && func(ary[i], i, ary)) {
                break;
            }
        }
    }

    /**
     * @return {Object}
     */
    function getSortedTrace() {
        return requirejs.s.contexts._.xray.traceOrder.sort(function (a, b) {
            return a.toLowerCase() > b.toLowerCase() ? 1 : -1;
        });
    }

    /**
     * @param  {String} id
     * @return {Object}
     */
    function getModule(id) {
        return requirejs.s.contexts._.xray.traced[id];
    }

    /**
     * Log trace into console
     * @param {Object} module
     */
    function logTrace(module) {
        if (module.map.parentMap) {
            console.group(module.map.id);
        } else {
            console.log(module.map.id);
        }

        if (module.map.parentMap) {
            var cleanId = module.map.parentMap.id.replace('mixins!', ''),
                _module = getModule(cleanId);

            if (_module) {
                logTrace(_module);
            } else {
                console.log(cleanId);
            }
        }

        if (module.map.parentMap) {
            console.groupEnd();
        }
    }

    /**
     * Log dependency tree into console
     * @param {Object} module
     * @param {Number} depth
     * @param {Number} currentLevel
     */
    function logTree(module, depth, currentLevel) {
        var id = module.map.id;

        currentLevel = currentLevel || 1;

        if (id.indexOf('_@r') === 0 || id.indexOf('text!') === 0) {
            return;
        }

        if (module.deps.length && currentLevel <= depth) {
            console.groupCollapsed(id);
        } else {
            console.log(id);
        }

        each(module.deps, function (dependency) {
            var cleanId = dependency.id.replace('mixins!', ''),
                _module = getModule(cleanId);

            if (currentLevel < depth && _module) {
                logTree(_module, depth, currentLevel + 1);
            } else {
                console.log(cleanId);
            }
        });

        if (module.deps.length) {
            console.groupEnd();
        }
    }

    /**
     * Find module dependencies
     * @param  {Object} module
     * @param  {Array} result
     * @param  {Array} processed
     * @return {Array}
     */
    function findDeps(module, result, processed) {
        var id = module.map.id;

        result = result || [];
        processed = processed || [];

        if (id.indexOf('_@r') === 0 || id.indexOf('text!') === 0) {
            return result;
        }

        each(module.deps, function (dependency) {
            var _module = getModule(dependency.id);

            result.push(dependency.id.replace('mixins!', ''));

            if (_module && processed.indexOf(dependency.id) === -1) {
                processed.push(dependency.id);
                findDeps(_module, result, processed);
            }
        });

        return result;
    }

    /**
     * @param {String} idFilter
     */
    xrayquire.trace = function (idFilter) {
        each(getSortedTrace(), function (id) {
            if (idFilter && !id.match(idFilter)) {
                return;
            }

            logTrace(getModule(id));
        });
    };

    /**
     * Log dependency tree into console
     * @param {String} idFilter
     * @param {Number} depth
     */
    xrayquire.tree = function (idFilter, depth) {
        each(getSortedTrace(), function (id) {
            if (idFilter && !id.match(idFilter)) {
                return;
            }

            logTree(getModule(id), depth || 1);
        });
    };

    /**
     * Search for cycle dependencies
     */
    xrayquire.cycles = function () {
        var module;

        each(getSortedTrace(), function (id) {
            module = getModule(id);

            if (findDeps(module).indexOf(id) > -1) {
                logTree(module, 7);
            }
        });
    };

    /**
     * Get all dependencies
     */
    xrayquire.deps = function (idFilter) {
        var result = [];

        each(getSortedTrace(), function (id) {
            var mod = getModule(id);

            if (idFilter && !id.match(idFilter)) {
                return;
            }

            result = result.concat(findDeps(mod));
        });

        console.log([...new Set(result)]);
    };
}());
