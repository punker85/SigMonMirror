create table if not exists scanner (
	id tinyint unsigned not null default '0' comment 'Identifier',
    owner char(50) not null default 'John Smith' comment 'Full name',
    model char(50) not null default 'Raspberry Pi' comment 'Device model',
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
    foreign key(device)
		references device(id)
        on delete cascade
) ;