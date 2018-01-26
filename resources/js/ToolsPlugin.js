Ext.ns('dvelum','dvelum.shop');
Ext.define('dvelum.shop.toolsPlugin',{
    extend: 'Ext.Editor',
    shim: false,
    labelSelector: '',
    autoSize: {
        width: 'boundEl'
    },
    //bubbleEvents:['toolClick'],
    init: function(view) {
        this.view = view;
        this.mon(view, 'afterrender', function(){
            this.mon(this.view.getEl(), {
                click: {
                    fn: this.onClick,
                    scope: this
                }
            });
        }, this);
    },
    // on mousedown show editor
    onClick: function(e, target) {
        var me = this;
        var	item, record;
        var node = Ext.fly(target);
        if (node.hasCls(me.labelSelector)) {
            e.stopEvent();
            item = me.view.findItemByChild(target);
            record = me.view.store.getAt(me.view.indexOf(item));
            this.fireEvent('toolClick' , record , node);
        }
    }
});