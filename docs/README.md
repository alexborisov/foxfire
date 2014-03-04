# FoxFire Documentation

###Introduction
* [Key Features](https://github.com/foxly/foxfire/blob/master/docs/base/key_features.md)
* Theory of Operation
* Installation

###Creating Tables
* [Data Class Layout](https://github.com/foxly/foxfire/blob/master/docs/tables/layout.md)
* [Table Definition Arrays](https://github.com/foxly/foxfire/blob/master/docs/tables/tables.md)

###Basic SQL Operations
* [Insert](https://github.com/foxly/foxfire/blob/master/docs/tables/op_insert.md)
* [Indate](https://github.com/foxly/foxfire/blob/master/docs/tables/op_indate.md)
* [Select](https://github.com/foxly/foxfire/blob/master/docs/tables/op_select.md)
 * [runSelectQuery](https://github.com/foxly/foxfire/blob/master/docs/tables/op_runSelectQuery.md)
 * [runSelectQueryCol](https://github.com/foxly/foxfire/blob/master/docs/tables/op_runSelectQueryCol.md)
 * [runSelectQueryJoin](https://github.com/foxly/foxfire/blob/master/docs/tables/op_runSelectQueryJoin.md)
* [Update](https://github.com/foxly/foxfire/blob/master/docs/tables/op_update.md)
* [Delete](https://github.com/foxly/foxfire/blob/master/docs/tables/op_delete.md)
* [Transactions](https://github.com/foxly/foxfire/blob/master/docs/tables/transactions.md)
* [Formatting Results](https://github.com/foxly/foxfire/blob/master/docs/tables/result_formatters.md)

###Advanced SQL Operations
* If-Else Selector
* LIKE
* MATCH AGAINST
* Graph Operators

###Cache Operations
* Cache Drivers
* Cache Engine Selection
* Cache Transactions
* Row Locking
* Namespace Locking

###The Data Silos
#####Paged Tries
 * [Paged L1](https://github.com/foxly/foxfire/blob/master/core/base_classes/abstract.class.datastore.paged.L1.php)
 * [Paged L2](https://github.com/foxly/foxfire/blob/master/core/base_classes/abstract.class.datastore.paged.L2.php)
 * [Paged L3](https://github.com/foxly/foxfire/blob/master/core/base_classes/abstract.class.datastore.paged.L3.php)
 * [Paged L4](https://github.com/foxly/foxfire/blob/master/core/base_classes/abstract.class.datastore.paged.L4.php)
 * [Paged L5](https://github.com/foxly/foxfire/blob/master/core/base_classes/abstract.class.datastore.paged.L5.php)

#####Monolithic Tries
 * [Mono L3](https://github.com/foxly/foxfire/blob/master/core/base_classes/abstract.class.datastore.monolithic.L3.php)

#####Dictionaries
 * [Double Linked](https://github.com/foxly/foxfire/blob/master/core/base_classes/abstract.class.dictionary.php)

###Troubleshooting & Debug
* Exception Chaining
* [Debug Flags](https://github.com/foxly/foxfire/blob/master/docs/tables/debug.md)
