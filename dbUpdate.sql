create table payment(rowid int auto_increment primary key
, pelunasan_id int
, payment_method_id int
, disburstment_date datetime
, nominal int
, created_at datetime DEFAULT CURRENT_TIMESTAMP 
, updated_at datetime  DEFAULT CURRENT_TIMESTAMP  on update current_timestamp
, updated_by int);
alter table buy_detail add pelunasan_id int;
create table payment_method(
rowid int auto_increment primary key
, payment_method_name varchar(50)
, created_at datetime  DEFAULT CURRENT_TIMESTAMP 
, updated_at datetime  DEFAULT CURRENT_TIMESTAMP  on update current_timestamp
, updated_by int);
create table pelunasan(rowid int auto_increment primary key
, supplier_id int
, created_at datetime  DEFAULT CURRENT_TIMESTAMP
, updated_at datetime  DEFAULT CURRENT_TIMESTAMP  on update current_timestamp
, updated_by int);
