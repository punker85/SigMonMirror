use sigmon;
create table if not exists scanner (
	id tinyint unsigned not null default '0' comment 'Identifier',
    owner char(50) not null default 'John Smith' comment 'Full name',
    model char(50) not null default 'Raspberry Pi' comment 'Device model',
    mac char(17) not null default 'FF:FF:FF:FF:FF:FF' comment 'MAC address',
    primary key (id)
) ;

create table if not exists experiment (
	id mediumint unsigned not null auto_increment comment 'Identifier',
    scanner tinyint unsigned not null default '0' comment 'Scanning device',
    time_start datetime(0) not null default '1000-01-01 00:00:00' comment 'Starting Time',
    location char(50) not null default 'Place' comment 'Nearby Landmark',
    lat decimal(13,10) not null default '0.0' comment 'Latitude',
    lng decimal(13,10) not null default '0.0' comment 'Longitude',
    primary key(id),
    constraint time_per_scanner unique (scanner, time_start),
    foreign key(scanner)
		references scanner(id)
        on delete cascade
) ;

create table if not exists device (
	id int unsigned not null auto_increment comment 'Identifier',
    experiment mediumint unsigned not null default '0' comment 'Experiment',
    discovery datetime(0) not null default '1000-01-01 00:00:00' comment 'Initial discovery',
    mac char(17) not null default 'FF:FF:FF:FF:FF:FF' comment 'MAC address',
    primary key(id),
    constraint single_dev_per_exp unique (experiment, mac),
    foreign key(experiment)
		references experiment(id)
        on delete cascade
) ;

create table if not exists rssi (
	id int unsigned not null auto_increment comment 'Identifier',
    device int unsigned not null default '0' comment 'Device',
    scan_update time(0) not null default '00:00:00' comment 'RSSI update',
    rssi tinyint not null default '0' comment 'Signal Strength',
    primary key(id),
    constraint single_dev_rssi_per_ms unique (device, scan_update, rssi),
    foreign key(device)
		references device(id)
        on delete cascade
) ;

create table if not exists triplet (
	id int unsigned not null auto_increment comment 'Identifier',
    exp1 mediumint unsigned comment 'Experiment 1',
    exp2 mediumint unsigned comment 'Experiment 2',
    exp3 mediumint unsigned comment 'Experiment 3',
    primary key(id),
    constraint single_triple unique (exp1, exp2, exp3),
    foreign key(exp1)
	references experiment(id)
        on delete set null,
    foreign key(exp2)
	references experiment(id)
        on delete set null,
    foreign key(exp3)
	references experiment(id)
        on delete set null
) ;

create table if not exists trilat (
	id int unsigned not null auto_increment comment 'Identifier',
    triple int unsigned not null default '0' comment 'Triple Experiment',
    mac char(17) not null default 'FF:FF:FF:FF:FF:FF' comment 'MAC address',
    time time(0) not null default '00:00:00' comment 'Time',
    lat decimal(13,10) not null default '0.0' comment 'Latitude',
    lng decimal(13,10) not null default '0.0' comment 'Longitude',
    primary key(id),
    constraint mac_per_time unique (triple, mac, time),
    foreign key(triple)
	references triplet(id)
        on delete no action
) ;

create table if not exists dist (
	id int unsigned not null auto_increment comment 'Identifier',
    experiment mediumint unsigned not null default '0' comment 'Experiment',
    mac char(17) not null default 'FF:FF:FF:FF:FF:FF' comment 'MAC address',
    start_time datetime(0) not null default '00:00:00' comment 'Time',
    distance decimal(5,2) not null default '0.0' comment 'Distance in meters',
    primary key(id),
    constraint mac_exp_at_time unique (experiment, mac, start_time),
    foreign key(experiment)
	references experiment(id)
        on delete cascade
) ;