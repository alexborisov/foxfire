Ext.require(['Ext.data.*', 'Ext.grid.*']);

Ext.define('Event', {
    extend: 'Ext.data.Model',
    fields: [
            {name: 'id', type: 'int'},
            {name: 'tree', type: 'int'},
            {name: 'branch', type: 'int'},
            {name: 'node', type: 'int'},
            {name: 'userid', type: 'int'},
            {name: 'level', type: 'int'},
            {name: 'date', type: 'string'},
            {name: 'summary', type: 'string'},
            {name: 'data', type: 'string'}
    ]
});

Ext.onReady(function(){

    var store = Ext.create('Ext.data.Store', {
        autoLoad: true,
        autoSync: true,
        model: 'Event',
        proxy: {
            type: 'rest',
            url: 'http://localhost/wp-content/plugins/foxfire/core/admin/page_system_tools/eventapp.php',
            reader: {
                type: 'json',
                root: 'data'
            },
            writer: {
                type: 'json'
            }
        },
        listeners: {
            write: function(store, operation){
                var record = operation.getRecords()[0],
                    name = Ext.String.capitalize(operation.action),
                    verb;


                if (name == 'Destroy') {
                    record = operation.records[0];
                    verb = 'Destroyed';
                } else {
                    verb = name + 'd';
                }
                Ext.example.msg(name, Ext.String.format("{0} user: {1}", verb, record.getId()));

            }
        }
    });

    var rowEditing = Ext.create('Ext.grid.plugin.RowEditing');

    var grid = Ext.create('Ext.grid.Panel', {
        renderTo: 'eventlogs',
        plugins: [rowEditing],
        width: 740,
        height: 300,
        frame: true,
        title: 'Users',
        store: store,
        iconCls: 'icon-user',
        columns: [
                {
                    id       :'id',
                    text   : 'ID',
                    width    : 50,
                    sortable : true,
                    dataIndex: 'id'
                },
                {
                    text   : 'Tree',
                    width    : 50,
                    sortable : true,
                    dataIndex: 'tree'
                },
                {
                    text   : 'Branch',
                    width    : 50,
                    sortable : true,
                    dataIndex: 'branch'
                },
                {
                    text   : 'Node',
                    width    : 50,
                    sortable : true,
                    dataIndex: 'node'
                },
                {
                    text   : 'User ID',
                    width    : 50,
                    sortable : true,
                    dataIndex: 'userid'
                },
                {
                    text   : 'Level',
                    width    : 50,
                    sortable : true,
                    dataIndex: 'level'
                },
                {
                    text   : 'Date',
                    width    : 140,
                    sortable : true,
                    dataIndex: 'date'
                },
                {
                    text   : 'Summary',
                    width    : 100,
                    sortable : true,
                    dataIndex: 'summary'
                },
                {
                    text   : 'Data',
                    width    : 178,
                    sortable : true,
                    dataIndex: 'data'
                },
            ],
        dockedItems: [{
            xtype: 'toolbar',
            items: [{
                text: 'Add',
                iconCls: 'icon-add',
                handler: function(){
                    // empty record
                    store.insert(0, new Event());
                    rowEditing.startEdit(0, 0);
                }
            }, '-', {
                itemId: 'delete',
                text: 'Delete',
                iconCls: 'icon-delete',
                disabled: true,
                handler: function(){
                    var selection = grid.getView().getSelectionModel().getSelection()[0];
                    if (selection) {
                        store.remove(selection);
                    }
                }
            }]
        }]
    });
    grid.getSelectionModel().on('selectionchange', function(selModel, selections){
        grid.down('#delete').setDisabled(selections.length === 0);
    });
});