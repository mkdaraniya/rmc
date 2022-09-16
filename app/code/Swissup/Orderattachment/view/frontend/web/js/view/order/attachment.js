define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiComponent'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_Orderattachment/order/attachment-markup'
        },
        isVisible: window.checkoutConfig.swissupAttachmentEnabled,

        initialize: function () {
            this._super();

            this.files = {};
            this.allowedExtensions = window.checkoutConfig.swissupAttachmentExt;
            this.maxFileSize = window.checkoutConfig.swissupAttachmentSize;
            this.removeItem = window.checkoutConfig.removeItem;
            this.maxFileLimit = window.checkoutConfig.swissupAttachmentLimit;
            this.invalidExtError = window.checkoutConfig.swissupAttachmentInvalidExt;
            this.invalidSizeError = window.checkoutConfig.swissupAttachmentInvalidSize;
            this.invalidLimitError = window.checkoutConfig.swissupAttachmentInvalidLimit;
            this.uploadUrl = window.checkoutConfig.swissupAttachmentUpload;
            this.updateUrl = window.checkoutConfig.swissupAttachmentUpdate;
            this.removeUrl = window.checkoutConfig.swissupAttachmentRemove;
            this.comment = window.checkoutConfig.swissupAttachmentComment;
            this.attachments = window.checkoutConfig.attachments;

            $.async('#order-attachment', this.prepareObservers.bind(this));
            $.async('.attachment-container', this.addUploadedItems.bind(this));
        },

        selectFiles: function () {
            $('#order-attachment').trigger('click');
        },

        dragEnter: function (data, event) {},

        dragOver: function (data, event) {},

        drop: function (data, event) {
            $('.order-attachment-drag-area').css('border', '2px dashed #1979c3');
            var droppedFiles = event.originalEvent.dataTransfer.files;
            for (var i = 0; i < droppedFiles.length; i++) {
                this.processingFile(droppedFiles[i]);
            }
        },

        prepareObservers: function () {
            var self = this;

            $(document).on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });
            $(document).on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });
            $(document).on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $('#order-attachment').on('change', function (event) {
                $.each(this.files, function (key, file) {
                    self.processingFile(file);
                });
            });
        },

        addUploadedItems: function () {
            var attachments = this.attachments,
                self = this;

            if (!attachments) {
                return;
            }

            for (var i in attachments) {
                if (!attachments.hasOwnProperty(i)) {
                    continue;
                }

                var attachment = attachments[i];
                var uniq = Math.random().toString(32).slice(2);
                self.files[uniq] = attachment.path;
                self.addAttachmentMarkup(uniq, attachment.path);
                self.addAttachmentContent(uniq, attachment);
            }
        },

        addAttachmentMarkup: function (pos, fileName) {
            var container = $('.attachment-container'),
                newRow = $('<div class="swissup-attachment-row" rel="' + pos + '"></div>').appendTo(container),
                loader = $('<div class="swissup-attachment-loader"><div class="circle"></div><div class="circle"></div><div class="circle"></div></div>').appendTo(newRow),
                rowContent = $('<div class="swissup-attachment-row-content"></div>').appendTo(newRow),
                preview = $('<div class="order-attachment-preview"></div>').appendTo(rowContent);

            $('<div class="order-attachment-content"></div>').appendTo(rowContent);
            var finfo = $('<div class="attachment-file"></div>').appendTo(preview);
            finfo.append('<div class="filename">' + fileName + "</div>");
        },

        addAttachmentContent: function (pos, attachment) {
            var self = this,
                row = $('div.swissup-attachment-row[rel="' + pos + '"]'),
                preview = row.find(".order-attachment-preview");

            this.previewFile(preview, attachment.type, attachment.preview);

            var content = row.find(".order-attachment-content"),
                attachId = attachment.attachment_id,
                html = '<textarea id="attachment-comment'+attachId+'" rows="4" name="attachment['+
                    attachId+'][comment]" class="comment" placeholder="'+this.comment+'">'+attachment.comment+'</textarea>' +
                    '<a id="swissup-attachment-remove'+attachId+'" class="swissup-attachment-remove" title="'+this.removeItem+'" href="#"></a>'+
                    '<input type="hidden" class="swissup-attachment-id'+attachId+'" name="attachment-id" value="'+attachId+'">' +
                    '<input type="hidden" class="swissup-attachment-hash'+attachId+'" name="attachment-hash" value="'+attachment.hash+'">';

            $(html).appendTo(content);
            this.hideRowLoader(row);

            var id = row.find('.swissup-attachment-id' + attachId).val(),
                hash = row.find('.swissup-attachment-hash' + attachId).val();

            $('#attachment-comment' + attachId).focusout(function () {
                if ($(this).val()) {
                    self.updateComment(id, hash, $(this).val(), pos);
                }
            });

            $('#swissup-attachment-remove' + attachId).on('click', function (event) {
                event.preventDefault();
                self.removeFile(id, hash, pos);
            });
        },

        showRowLoader: function (row) {
            var loader = row.find('.swissup-attachment-loader');

            $(loader).css({
                    visibility: 'visible',
                    opacity: 0.0
                })
                .animate({
                    opacity: 1.0
                }, 300);
        },

        hideRowLoader: function (row) {
            var loader = row.find('.swissup-attachment-loader');

            $(loader).animate({ opacity: 0 }, 300, function () {
                $(loader).css('visibility', 'hidden');
            });
        },

        processingFile: function (file) {
            var error = this.validateFile(file);

            if (error) {
                this.addError(error);
            } else {
                var filesLen = Object.keys(this.files).length;
                if (Object.keys(this.files).length >= this.maxFileLimit) {
                    this.addError(this.invalidLimitError);
                } else {
                    var uniq = Math.random().toString(32).slice(2);
                    this.files[uniq] = file.name;
                    this.addAttachmentMarkup(uniq, file.name);
                    this.upload(file, uniq);
                }
            }
        },

        upload: function (file, pos) {
            var formAttach = new FormData(),
                self = this,
                row = $('div.swissup-attachment-row[rel="' + pos + '"]');

            this.showRowLoader(row);
            formAttach.append($('#order-attachment').attr('name'), file);

            if (window.FORM_KEY) {
                formAttach.append('form_key', window.FORM_KEY);
            }

            $.ajax({
                url: this.uploadUrl,
                global: false,
                type: 'post',
                data: formAttach,
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result.success) {
                        self.addAttachmentContent(pos, result);
                    } else {
                        self.addError(result.error);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    self.addError(thrownError);
                    delete this.files[pos];
                    $('div.swissup-attachment-row[rel="' + pos + '"]').remove();
                },
                cache: false,
                contentType: false,
                processData: false
            });
        },

        updateComment: function (id, hash, comment, pos) {
            var attachParams = {
                    'attachment': id,
                    'hash': hash,
                    'comment': comment,
                    'form_key': window.FORM_KEY
                },
                self = this,
                row = $('div.swissup-attachment-row[rel="' + pos + '"]');
                this.showRowLoader(row);

            $.ajax({
                url: this.updateUrl,
                global: false,
                type: "post",
                data: $.param(attachParams),
                success: function (data) {
                    var result = JSON.parse(data);
                    if (!result.success) {
                        self.addError(result.error);
                    }
                    self.hideRowLoader(row);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    self.addError(thrownError);
                }
            });

        },

        removeFile: function (id, hash, pos) {
            var attachParams = {
                    'attachment': id,
                    'hash': hash,
                    'form_key': window.FORM_KEY
                },
                self = this,
                row = $('div.swissup-attachment-row[rel="' + pos + '"]');
                this.showRowLoader(row);

            $.ajax({
                url: this.removeUrl,
                global: false,
                type: "post",
                data: $.param(attachParams),
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result.success) {
                        delete self.files[pos];
                        row.fadeOut('500', function () {
                            $(this).remove();
                        });
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    self.addError(thrownError);
                }
            });
        },

        previewFile: function (container, attachType, fileUrl) {
            var type = attachType.split('/')[0],
                c = container.find('.attachment-file'),
                prev = '';

            switch (type) {
                case 'image':
                    prev = $('<img class="thumbnail" src="' + fileUrl + '" />').insertBefore(c);
                break;

                case 'video':
                    prev = $('<video src="' + fileUrl + '" width="100%" controls></video>').insertBefore(c);
                break;

                case 'audio':
                    prev = $('<audio src="' + fileUrl + '" style="display:block; width:100%;" controls></audio>').insertBefore(c);
                  break;

                default:
                    prev = $('<div class="swissup-attachment-default-preview"></div>').insertBefore(c);
                  break;
            }
        },

        validateFile: function (file) {
            if (!this.checkFileExtension(file)) {
                return this.invalidExtError;
            }

            if (!this.checkFileSize(file)) {
                return this.invalidSizeError;
            }

            return null;
        },

        checkFileExtension: function (file) {
            var fileExt = file.name.split('.').pop().toLowerCase();
            var allowedExt = this.allowedExtensions.split(',');
            if (-1 == $.inArray(fileExt, allowedExt)) {
                return false;
            }
            return true;
        },

        checkFileSize: function (file) {
            if ((file.size / 1024) > this.maxFileSize) {
                return false;
            }
            return true;
        },

        addError: function (error) {
            var html = null;
            html = '<div class="swissup-attachment-error danger">'+ error +'</div>';
            $('.attachment-container').before(html);
            $('.swissup-attachment-error .close').on('click', function () {
                var el = $(this).closest('div');
                if (el.hasClass('swissup-attachment-error')) {
                    $(el).slideUp('slow', function () {
                        $(this).remove();
                    });
                }
            });
        }
    });
});
