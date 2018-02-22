( function ($) {
    "use strict";


    window.VCOwlSliderView = vc.shortcode_view.extend({
        adding_new_tab: false,
        events: {
            'click .add_tab': 'addTab',
            'click > .vc_controls .column_delete, > .vc_controls .vc_control-btn-delete': 'deleteShortcode',
            'click > .vc_controls .column_edit, > .vc_controls .vc_control-btn-edit': 'editElement',
            'click > .vc_controls .column_clone,> .vc_controls .vc_control-btn-clone': 'clone'
        },
        render: function () {
            window.VCOwlSliderView.__super__.render.call(this);
            this.$content.sortable({
                axis: "y",
                handle: ".wpb_element_wrapper,.vc_element-move",
                stop: function (event, ui) {
                    // IE doesn't register the blur when sorting
                    // so trigger focusout handlers to remove .ui-state-focus
                    ui.item.prev().triggerHandler("focusout");
                    $(this).find('> .wpb_sortable').each(function () {
                        var shortcode = $(this).data('model');
                        shortcode.save({'order': $(this).index()}); // Optimize
                    });
                }
            });
            return this;
        },
        changeShortcodeParams: function (model) {
            window.VCOwlSliderView.__super__.changeShortcodeParams.call(this, model);
            var collapsible = _.isString(this.model.get('params').collapsible) && this.model.get('params').collapsible === 'yes' ? true : false;
            if ( this.$content.hasClass('ui-accordion') ) {
                this.$content.accordion("option", "collapsible", collapsible);
            }
        },
        changedContent: function (view) {
            if ( this.$content.hasClass('ui-accordion') ) this.$content.accordion('destroy');
            var collapsible = _.isString(this.model.get('params').collapsible) && this.model.get('params').collapsible === 'yes' ? true : false;
            this.$content.accordion({
                header: "h3",
                navigation: false,
                autoHeight: true,
                heightStyle: "content",
                collapsible: collapsible,
                active: this.adding_new_tab === false && view.model.get('cloned') !== true ? 0 : view.$el.index()
            });
            this.adding_new_tab = false;
        },
        addTab: function (e) {
            this.adding_new_tab = true;
            e.preventDefault();
            vc.shortcodes.create({
                shortcode: 'vc_owlcarousel_item',
                params: {name: 'New Slide', content: 'Click edit button to change this text.'},
                parent_id: this.model.id
            });
        },
        _loadDefaults: function () {
            window.VCOwlSliderView.__super__._loadDefaults.call(this);
        }
    });

} )(window.jQuery);
