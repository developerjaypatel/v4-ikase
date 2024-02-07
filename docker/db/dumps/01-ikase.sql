create table az_case_notes
(
    case_notes_id int auto_increment
        primary key,
    case_notes_uuid varchar(15) charset latin1 not null,
    case_uuid varchar(15) charset latin1 not null,
    notes_uuid varchar(255) charset latin1 not null,
    attribute varchar(255) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on az_case_notes (attribute);

create index case_uuid
    on az_case_notes (case_uuid);

create index notes_uuid
    on az_case_notes (notes_uuid);

create table az_notes
(
    notes_id int auto_increment
        primary key,
    notes_uuid varchar(255) charset latin1 default '' not null,
    type varchar(100) charset latin1 default 'general' not null,
    subject varchar(255) default '' not null,
    note longtext charset latin1 not null,
    title varchar(255) charset latin1 default '' not null,
    attachments varchar(1055) default '' not null,
    entered_by varchar(255) charset latin1 default 'SYSTEM' not null,
    status varchar(50) charset latin1 default 'STANDARD' not null,
    dateandtime timestamp default CURRENT_TIMESTAMP not null,
    callback_date datetime default '0000-00-00 00:00:00' not null,
    verified enum('Y', 'N') charset latin1 default 'N' not null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on az_notes (customer_id);

create index note_uuid
    on az_notes (notes_uuid);

create index type
    on az_notes (type);

create table cards
(
    `First Name` varchar(12) null,
    `Last Name` varchar(13) null,
    `Job Title` varchar(64) null,
    `Company Name` varchar(45) null,
    Email varchar(38) null,
    `Street 1` varchar(45) null,
    `Street 2` varchar(32) null,
    City varchar(14) null,
    State varchar(10) null,
    Zip varchar(10) null,
    Phone varchar(24) null,
    Mobile varchar(14) null,
    Fax varchar(18) null
)
    engine=MyISAM;

create table cse_accident
(
    accident_id int auto_increment
        primary key,
    accident_uuid varchar(15) collate utf8_unicode_ci default '' null,
    accident_date datetime default '0000-00-00 00:00:00' null,
    accident_description text null,
    accident_info text null,
    accident_details text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
);

create table cse_accident_original
(
    accident_id int auto_increment
        primary key,
    accident_uuid varchar(15) null,
    accident_date datetime null,
    accident_location varchar(255) null,
    road_condition varchar(255) null,
    traffic_controls varchar(255) null,
    client_street varchar(255) null,
    defendant_street varchar(255) null,
    client_speed varchar(255) null,
    client_direction varchar(255) null,
    client_lane varchar(255) null,
    defendant_speed varchar(255) null,
    defendant_direction varchar(255) null,
    defendant_lane varchar(255) null,
    accident_weather varchar(255) null,
    nature_of_trip varchar(255) null,
    accident_description text null,
    other_details text null,
    customer_id int null,
    deleted enum('N', 'Y') default 'N' null
);

create table cse_accident_track
(
    accident_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    accident_id int not null,
    accident_uuid varchar(15) null,
    accident_date datetime null,
    accident_location varchar(255) null,
    road_condition varchar(255) null,
    traffic_controls varchar(255) null,
    client_street varchar(255) null,
    defendant_street varchar(255) null,
    client_speed varchar(255) null,
    client_direction varchar(255) null,
    client_lane varchar(255) null,
    defendant_speed varchar(255) null,
    defendant_direction varchar(255) null,
    defendant_lane varchar(255) null,
    accident_weather varchar(255) null,
    nature_of_trip varchar(255) null,
    accident_description text null,
    other_details text null,
    customer_id int null,
    deleted enum('N', 'Y') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_account
(
    account_id int auto_increment
        primary key,
    account_uuid varchar(15) default '' null,
    account_name varchar(255) default '' null,
    account_type enum('operating', 'trust') charset utf8 default 'operating' null,
    starting_statement_date date default '0000-00-00' null,
    starting_amount decimal(9,2) default 0.00 null,
    account_balance decimal(7,2) default 0.00 null,
    account_info varchar(1055) charset utf8 default '' null comment 'json format',
    customer_id int default 0 null,
    deleted enum('Y', 'N') charset utf8 default 'N' null
)
    comment 'bank accounts' collate=utf8_unicode_ci;

create table cse_account_adjustment
(
    account_adjustment_id int auto_increment
        primary key,
    account_adjustment_uuid varchar(15) not null,
    account_uuid varchar(15) not null,
    adjustment_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index account_uuid
    on cse_account_adjustment (account_uuid);

create index adjustment_uuid
    on cse_account_adjustment (adjustment_uuid);

create index attribute
    on cse_account_adjustment (attribute);

create index customer_id
    on cse_account_adjustment (customer_id);

create index deleted
    on cse_account_adjustment (deleted);

create table cse_account_check
(
    account_check_id int auto_increment
        primary key,
    account_check_uuid varchar(15) not null,
    account_uuid varchar(15) not null,
    check_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index account_uuid
    on cse_account_check (account_uuid);

create index attribute
    on cse_account_check (attribute);

create index check_uuid
    on cse_account_check (check_uuid);

create index customer_id
    on cse_account_check (customer_id);

create index deleted
    on cse_account_check (deleted);

create table cse_account_checkrequest
(
    account_checkrequest_id int auto_increment
        primary key,
    account_checkrequest_uuid varchar(15) not null,
    account_uuid varchar(15) not null,
    checkrequest_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index account_uuid
    on cse_account_checkrequest (account_uuid);

create index attribute
    on cse_account_checkrequest (attribute);

create index checkrequest_uuid
    on cse_account_checkrequest (checkrequest_uuid);

create index customer_id
    on cse_account_checkrequest (customer_id);

create index deleted
    on cse_account_checkrequest (deleted);

create table cse_account_kinvoice
(
    account_kinvoice_id int auto_increment
        primary key,
    account_kinvoice_uuid varchar(15) not null,
    account_uuid varchar(15) not null,
    kinvoice_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index account_uuid
    on cse_account_kinvoice (account_uuid);

create index attribute
    on cse_account_kinvoice (attribute);

create index customer_id
    on cse_account_kinvoice (customer_id);

create index deleted
    on cse_account_kinvoice (deleted);

create index kinvoice_uuid
    on cse_account_kinvoice (kinvoice_uuid);

create table cse_account_track
(
    account_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    account_id int not null,
    account_uuid varchar(15) default '' null,
    account_name varchar(255) default '' null,
    account_type enum('operating', 'trust') default 'operating' null,
    starting_statement_date date default '0000-00-00' null,
    starting_amount decimal(9,2) default 0.00 null,
    account_balance decimal(7,2) default 0.00 null,
    account_info varchar(1055) default '' null comment 'json format',
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_active_users
(
    active_id int auto_increment
        primary key,
    customer_id int default 0 null,
    active_year int default 0 null,
    active_month int default 0 null,
    user_count int default 0 null,
    user_names varchar(1055) default '' null
)
    comment 'Keep track of active users per customer id per month';

create table cse_activity
(
    activity_id int auto_increment
        primary key,
    activity_uuid varchar(15) default '' not null,
    activity longtext not null,
    activity_category varchar(100) default '' not null,
    activity_date timestamp default CURRENT_TIMESTAMP not null,
    hours decimal(7,3) default 0.000 null,
    timekeeper varchar(30) default '' null,
    initials varchar(10) default '' null,
    attorney varchar(10) default '' null,
    activity_user_id int default 0 not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null,
    activity_status varchar(255) null,
    billing_rate varchar(255) null,
    billing_amount decimal(7,2) default 0.00 null,
    billing_unit varchar(45) default '' null,
    billing_date datetime null,
    constraint activity_uuid
        unique (activity_uuid)
)
    comment 'activity for a case' engine=MyISAM collate=utf8_unicode_ci;

create index activity_category
    on cse_activity (activity_category);

create index activity_date
    on cse_activity (activity_date);

create index activity_user_id
    on cse_activity (activity_user_id);

create index customer_id
    on cse_activity (customer_id);

create index deleted
    on cse_activity (deleted);

create table cse_activity_track
(
    activity_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    activity_id int not null,
    activity_uuid varchar(15) not null,
    activity longtext not null,
    activity_category varchar(100) default '' not null,
    activity_date date default '0000-00-00' not null,
    hours decimal(7,3) default 0.000 null,
    timekeeper varchar(30) default '' null,
    initials varchar(10) default '' null,
    attorney varchar(10) default '' null,
    activity_user_id int default 0 not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null,
    activity_status varchar(255) null,
    billing_rate varchar(255) null,
    billing_date datetime null
)
    comment 'track activity for a case' engine=MyISAM collate=utf8_unicode_ci;

create table cse_adhoc
(
    adhoc_id int auto_increment
        primary key,
    adhoc_uuid varchar(15) not null,
    adhoc varchar(100) not null,
    type enum('checkbox', 'radio', 'select', 'text', 'text_area') default 'text' not null,
    acceptable_values varchar(255) not null,
    default_value varchar(100) not null,
    format enum('', 'phone', 'date', 'integer', 'decimal') default '' not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_adjustment
(
    adjustment_id int auto_increment
        primary key,
    adjustment_uuid varchar(15) default '' null,
    adjustment_date datetime default '0000-00-00 00:00:00' null,
    amount decimal(9,2) default 0.00 null,
    adjustment_type enum('A', 'I') default 'A' null comment 'A = Adjustment
I = Interest',
    description varchar(1055) default '' null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    comment 'adjustment to bank account balances' collate=utf8_unicode_ci;

create index customer_id
    on cse_adjustment (customer_id);

create index deleted
    on cse_adjustment (deleted);

create index uuid
    on cse_adjustment (adjustment_uuid);

create table cse_adjustment_track
(
    adjustment_track_id int auto_increment
        primary key,
    user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    adjustment_id int not null,
    adjustment_uuid varchar(15) default '' null,
    adjustment_date datetime default '0000-00-00 00:00:00' null,
    amount decimal(9,2) default 0.00 null,
    adjustment_type enum('A', 'I') default 'A' null comment 'A = Adjustment
I = Interest',
    description varchar(1055) default '' null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index adjustment_id
    on cse_adjustment_track (adjustment_id);

create index customer_id
    on cse_adjustment_track (customer_id);

create index operation
    on cse_adjustment_track (operation);

create index user_uuid
    on cse_adjustment_track (user_uuid);

create table cse_attorney
(
    attorney_id int auto_increment
        primary key,
    user_id int not null,
    customer_id int not null,
    firm_name varchar(100) not null,
    first_name varchar(100) not null,
    last_name varchar(100) not null,
    middle_initial char not null,
    aka varchar(4) not null,
    phone varchar(50) not null,
    fax varchar(50) not null,
    email varchar(100) not null,
    active enum('Y', 'N') default 'Y' not null,
    attorney_username varchar(255) not null,
    attorney_password varchar(255) not null,
    default_attorney enum('Y', 'N') default 'N' not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_attorney (customer_id);

create index user_id
    on cse_attorney (user_id);

create table cse_batchscan
(
    batchscan_id int auto_increment
        primary key,
    dateandtime timestamp default CURRENT_TIMESTAMP not null,
    filename varchar(255) default '' not null,
    time_stamp int default 0 not null,
    pages int default 0 not null,
    consideration varchar(100) default '' not null,
    attempted varchar(100) default '' not null,
    completion varchar(100) default '' not null,
    `match` varchar(100) default '' not null,
    separators varchar(1055) default '' not null,
    stacks varchar(100) default '' not null,
    stitched varchar(100) default '' not null,
    customer_id int default 0 not null,
    readimage datetime default '0000-00-00 00:00:00' not null,
    processed datetime default '0000-00-00 00:00:00' not null,
    separated enum('Y', 'N') default 'N' not null,
    stacked enum('Y', 'N', 'P') default 'N' not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_batchscan_calls
(
    call_date timestamp default CURRENT_TIMESTAMP null,
    uri varchar(45) default '' null,
    request varchar(1055) null
);

create table cse_batchscan_queue
(
    queue_id int auto_increment
        primary key,
    queue_uuid varchar(45) default '' null,
    stored_file varchar(255) default '' null,
    queue_date datetime default '0000-00-00 00:00:00' null,
    user_id int default 0 null,
    user_name varchar(255) default '' null,
    timestamp varchar(45) default '' null,
    pages int default 0 null,
    separators int default 0 null,
    documents int default 0 null,
    queue_status enum('QUEUE', 'OPEN', 'PROCESSED', 'CANCELLED') default 'QUEUE' null,
    customer_id int default 0 null,
    constraint queue_uuid
        unique (queue_uuid)
);

create index customer_id
    on cse_batchscan_queue (customer_id);

create index queue_status
    on cse_batchscan_queue (queue_status);

create table cse_batchscan_queue_track
(
    track_id int auto_increment
        primary key,
    queue_uuid varchar(45) null,
    description varchar(255) default '' null,
    microtime decimal(14,4) default 0.0000 null,
    time_stamp timestamp default CURRENT_TIMESTAMP null,
    ip_address varchar(45) default '' null
);

create table cse_batchscan_track
(
    batchscan_track_id int auto_increment
        primary key,
    user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp_track timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    batchscan_id int not null,
    dateandtime timestamp default '0000-00-00 00:00:00' not null,
    filename varchar(255) default '' not null,
    time_stamp int default 0 not null,
    pages int default 0 not null,
    consideration varchar(100) default '' not null,
    attempted varchar(100) default '' not null,
    completion varchar(100) default '' not null,
    `match` varchar(100) default '' not null,
    separators varchar(1055) default '' not null,
    stacks varchar(100) default '' not null,
    stitched varchar(100) default '' not null,
    customer_id int default 0 not null,
    readimage datetime default '0000-00-00 00:00:00' not null,
    processed datetime default '0000-00-00 00:00:00' not null,
    separated enum('Y', 'N') default 'N' not null,
    stacked enum('Y', 'N', 'P') default 'N' not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index batchscan_id
    on cse_batchscan_track (batchscan_id);

create index operation
    on cse_batchscan_track (operation);

create index user_uuid
    on cse_batchscan_track (user_uuid);

create table cse_billing
(
    billing_id int auto_increment
        primary key,
    billing_uuid varchar(15) null,
    activity_uuid varchar(15) null,
    billing_date datetime default '0000-00-00 00:00:00' not null,
    duration int default 0 null,
    status varchar(45) default '' null,
    billing_rate varchar(45) default '' null,
    activity_code varchar(45) default '' null,
    timekeeper varchar(45) default '' null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' null,
    case_id int null,
    description longtext null,
    action_id int null,
    action_type varchar(45) null,
    constraint billing_uuid_UNIQUE
        unique (billing_uuid)
);

create table cse_blocked
(
    blocked_id int auto_increment
        primary key,
    blocked_uuid varchar(15) default '' null,
    start_date datetime default '0000-00-00 00:00:00' null,
    end_date datetime default '0000-00-00 00:00:00' null,
    recurring_count int default 0 null comment 'number of times a recurring block occurs, -99 is forever',
    recurring_span enum('week', '2_weeks', 'month', '') charset utf8 default '' null comment 'every week, 2 weeks, month',
    customer_id int default 0 null,
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    constraint blocked_uuid
        unique (blocked_uuid)
)
    collate=utf8_unicode_ci;

create table cse_blocked_track
(
    blocked_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    blocked_id int default 0 not null,
    blocked_uuid varchar(15) default '' null,
    start_date datetime default '0000-00-00 00:00:00' null,
    end_date datetime default '0000-00-00 00:00:00' null,
    recurring_count int default 0 null comment 'number of times a recurring block occurs, -99 is forever',
    recurring_span enum('week', '2_weeks', 'month', '') default '' null comment 'every week, 2 weeks, month',
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_bodyparts
(
    bodyparts_id int auto_increment
        primary key,
    bodyparts_uuid varchar(15) not null,
    code int not null,
    description varchar(255) charset utf8 null,
    constraint bodyparts_uuid
        unique (bodyparts_uuid)
)
    collate=utf8_unicode_ci;

create table cse_bodyparts_track
(
    bodyparts_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    bodyparts_id int not null,
    bodyparts_uuid varchar(15) not null,
    code int not null,
    description varchar(255) null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_buffer
(
    buffer_id int auto_increment
        primary key,
    message_uuid varchar(15) not null,
    `from` varchar(255) not null,
    from_address varchar(255) default '' not null,
    recipients text not null,
    `to` varchar(255) default '' not null,
    cc varchar(255) default '' not null,
    bcc varchar(255) default '' not null,
    subject varchar(255) not null,
    message text not null,
    attachments varchar(255) default '' not null,
    timestamp timestamp default CURRENT_TIMESTAMP not null,
    buffer_error varchar(1055) default '' null,
    customer_id int not null,
    deleted enum('Y', 'N', 'E') default 'N' not null comment 'E for error'
)
    engine=MyISAM collate=utf8_unicode_ci;

create index message_uuid
    on cse_buffer (message_uuid);

create table cse_calendar
(
    calendar_id int auto_increment
        primary key,
    calendar_uuid varchar(15) default '' not null,
    calendar varchar(255) default '' not null,
    sort_order int default 99 not null,
    customer_id int default 0 not null,
    mandatory enum('Y', 'N') default 'N' not null,
    active enum('Y', 'N') default 'Y' not null,
    deleted enum('Y', 'N') default 'N' not null,
    constraint calendar_uuid
        unique (calendar_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_calendar_event
(
    calendar_event_id int auto_increment
        primary key,
    calendar_event_uuid varchar(55) not null,
    calendar_uuid varchar(15) not null,
    event_uuid varchar(155) default '' not null,
    user_uuid varchar(155) default '' not null comment 'for employee calendar',
    attribute varchar(255) default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(45) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index calendar_user_uuid
    on cse_calendar_event (user_uuid);

create index calendar_uuid
    on cse_calendar_event (calendar_uuid);

create index event_uuid
    on cse_calendar_event (event_uuid);

create table cse_case
(
    case_id int auto_increment,
    case_uuid varchar(15) not null,
    case_number varchar(255) null,
    file_number varchar(255) default '' null,
    cpointer varchar(255) default '0' not null,
    case_name varchar(255) default '' not null,
    source varchar(50) default '' not null,
    adj_number varchar(255) default '' not null,
    case_date date default '0000-00-00' null,
    filing_date date default '0000-00-00' null,
    terminated_date date default '0000-00-00' null,
    case_type varchar(255) null,
    injury_type varchar(150) default '' null,
    venue varchar(255) null,
    dois varchar(255) default '' not null,
    case_status varchar(255) default '' null,
    case_substatus varchar(255) default '' not null,
    case_subsubstatus varchar(255) default '' null,
    rating enum('A', 'B', 'C', 'D', 'F', '') default '' not null,
    submittedOn datetime default '0000-00-00 00:00:00' not null,
    supervising_attorney varchar(75) default '' null,
    attorney varchar(75) default '' not null,
    worker varchar(75) default '' not null,
    medical varchar(10) default '' not null,
    td varchar(10) default '' not null,
    rehab varchar(10) default '' not null,
    edd varchar(10) default '' not null,
    claims varchar(255) default '' not null,
    interpreter_needed enum('Y', 'N') default 'N' null,
    file_location varchar(45) default '' null,
    case_language varchar(100) default '' null,
    lien_filed enum('Y', 'N') default 'N' not null,
    sub_in enum('Y', 'N') default 'N' null,
    special_instructions varchar(1055) default '' null,
    case_description varchar(1055) default '' null comment 'keep new fields describing the case in json format',
    customer_id int default 0 not null,
    closed enum('Y', 'N') default 'N' null,
    deleted enum('Y', 'N') default 'N' not null,
    constraint case_id
        unique (case_id),
    constraint case_uuid
        unique (case_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attorney
    on cse_case (attorney);

create index case_date
    on cse_case (case_date);

create index case_number
    on cse_case (case_number);

create index case_status
    on cse_case (case_status);

create index case_type
    on cse_case (case_type);

create index closed
    on cse_case (closed);

create index customer_id
    on cse_case (customer_id);

create index deleted
    on cse_case (deleted);

create index file_number
    on cse_case (file_number);

create index supervising_attorney
    on cse_case (supervising_attorney);

create index worker
    on cse_case (worker);

create table cse_case_account
(
    case_account_id int auto_increment
        primary key,
    case_account_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    account_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index account_uuid
    on cse_case_account (account_uuid);

create index attribute
    on cse_case_account (attribute);

create index case_uuid
    on cse_case_account (case_uuid);

create index customer_id
    on cse_case_account (customer_id);

create index deleted
    on cse_case_account (deleted);

create table cse_case_activity
(
    case_activity_id int auto_increment
        primary key,
    case_activity_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    activity_uuid varchar(15) not null,
    attribute varchar(200) not null,
    case_track_id int default 0 not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index activity_uuid
    on cse_case_activity (activity_uuid);

create index case_track_uuid
    on cse_case_activity (case_track_id);

create index case_uuid
    on cse_case_activity (case_uuid);

create index deleted
    on cse_case_activity (deleted);

create table cse_case_bodyparts
(
    case_bodyparts_id int auto_increment
        primary key,
    case_bodyparts_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    bodyparts_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index bodyparts_uuid
    on cse_case_bodyparts (bodyparts_uuid);

create index case_uuid
    on cse_case_bodyparts (case_uuid);

create table cse_case_check
(
    case_check_id int auto_increment
        primary key,
    case_check_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    check_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_case_check (attribute);

create index case_uuid
    on cse_case_check (case_uuid);

create index check_uuid
    on cse_case_check (check_uuid);

create index customer_id
    on cse_case_check (customer_id);

create index deleted
    on cse_case_check (deleted);

create table cse_case_checkrequest
(
    case_checkrequest_id int auto_increment
        primary key,
    case_checkrequest_uuid varchar(15) charset latin1 not null,
    case_uuid varchar(15) charset latin1 not null,
    checkrequest_uuid varchar(25) default '' null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_checkrequest (case_uuid);

create index checkrequest_uuid
    on cse_case_checkrequest (checkrequest_uuid);

create table cse_case_corporation
(
    case_corporation_id int auto_increment
        primary key,
    case_corporation_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    corporation_uuid varchar(15) not null,
    injury_uuid varchar(15) default '' null comment 'corporations associated with the case might be exclusively matched to a specific injury',
    attribute varchar(100) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(45) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute2
    on cse_case_corporation (attribute);

create index case_uuid
    on cse_case_corporation (case_uuid);

create index corporation_uuid
    on cse_case_corporation (corporation_uuid);

create index deleted
    on cse_case_corporation (deleted);

create index injury_uuid
    on cse_case_corporation (injury_uuid);

create table cse_case_deduction
(
    case_deduction_id int auto_increment
        primary key,
    case_deduction_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    deduction_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_case_deduction (attribute);

create index case_uuid
    on cse_case_deduction (case_uuid);

create index customer_id
    on cse_case_deduction (customer_id);

create index deduction_uuid
    on cse_case_deduction (deduction_uuid);

create index deleted
    on cse_case_deduction (deleted);

create table cse_case_disability
(
    case_disability_id int auto_increment
        primary key,
    case_disability_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    disability_uuid varchar(55) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(254) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_disability (case_uuid);

create index disability_uuid
    on cse_case_disability (disability_uuid);

create table cse_case_document
(
    case_document_id int auto_increment
        primary key,
    case_document_uuid varchar(15) not null,
    case_uuid varchar(15) default '' not null,
    document_uuid varchar(15) default '' not null,
    attribute_1 varchar(30) default '' not null,
    attribute_2 varchar(30) default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute_1
    on cse_case_document (attribute_1);

create index case_uuid
    on cse_case_document (case_uuid);

create index customer_id
    on cse_case_document (customer_id);

create index deleted
    on cse_case_document (deleted);

create index document_uuid
    on cse_case_document (document_uuid);

create index last_update_user
    on cse_case_document (last_update_user);

create table cse_case_eamsinfo
(
    case_eamsinfo_id int auto_increment
        primary key,
    case_eamsinfo_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    eamsinfo_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_eamsinfo (case_uuid);

create index eamsinfo_uuid
    on cse_case_eamsinfo (eamsinfo_uuid);

create table cse_case_email
(
    case_email_id int auto_increment
        primary key,
    case_email_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    email_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_email (case_uuid);

create index email_uuid
    on cse_case_email (email_uuid);

create table cse_case_event
(
    case_event_id int auto_increment
        primary key,
    case_event_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    event_uuid varchar(155) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_event (case_uuid);

create index event_uuid
    on cse_case_event (event_uuid);

create table cse_case_homemedical
(
    case_homemedical_id int auto_increment
        primary key,
    case_homemedical_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    homemedical_uuid varchar(15) not null,
    attribute varchar(20) not null,
    case_track_id int default 0 not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_case_injury
(
    case_injury_id int auto_increment
        primary key,
    case_injury_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(45) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_injury (case_uuid);

create index deleted
    on cse_case_injury (deleted);

create index injury_uuid
    on cse_case_injury (injury_uuid);

create table cse_case_injury_number
(
    case_injury_number_id int auto_increment
        primary key,
    case_injury_number_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    injury_number_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_injury_number (case_uuid);

create index injury_number_uuid
    on cse_case_injury_number (injury_number_uuid);

create table cse_case_kinvoice
(
    case_kinvoice_id int auto_increment
        primary key,
    case_kinvoice_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    kinvoice_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_case_kinvoice (attribute);

create index case_uuid
    on cse_case_kinvoice (case_uuid);

create index customer_id
    on cse_case_kinvoice (customer_id);

create index deleted
    on cse_case_kinvoice (deleted);

create index kinvoice_uuid
    on cse_case_kinvoice (kinvoice_uuid);

create table cse_case_letter
(
    case_letter_id int auto_increment
        primary key,
    case_letter_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    letter_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_letter (case_uuid);

create index letter_uuid
    on cse_case_letter (letter_uuid);

create table cse_case_lostincome
(
    case_lostincome_id int auto_increment
        primary key,
    case_lostincome_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    lostincome_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_lostincome (case_uuid);

create index deleted
    on cse_case_lostincome (deleted);

create index lostincome_uuid
    on cse_case_lostincome (lostincome_uuid);

create table cse_case_matrixorder
(
    case_matrixorder_id int auto_increment
        primary key,
    case_id int null,
    order_id int null comment 'order id from matrix_empire',
    order_date date default '0000-00-00' null,
    order_info text null comment 'order info in json format',
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
);

create index case_id
    on cse_case_matrixorder (case_id);

create table cse_case_matrixrequest
(
    case_matrixrequest_id int auto_increment
        primary key,
    case_id int null,
    request_id int null comment 'request id from matrix_national',
    request_date timestamp default CURRENT_TIMESTAMP null,
    request_by varchar(4) null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
);

create table cse_case_medicalbilling
(
    case_medicalbilling_id int auto_increment
        primary key,
    case_medicalbilling_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    medicalbilling_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_medicalbilling (case_uuid);

create index deleted
    on cse_case_medicalbilling (deleted);

create index medicalbilling_uuid
    on cse_case_medicalbilling (medicalbilling_uuid);

create table cse_case_message
(
    case_message_id int auto_increment
        primary key,
    case_message_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    message_uuid varchar(155) not null,
    attribute varchar(255) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_message (case_uuid);

create index deleted
    on cse_case_message (deleted);

create index message_uuid
    on cse_case_message (message_uuid);

create table cse_case_negotiation
(
    case_negotiation_id int auto_increment
        primary key,
    case_negotiation_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    negotiation_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_negotiation (case_uuid);

create index deleted
    on cse_case_negotiation (deleted);

create index negotiation_uuid
    on cse_case_negotiation (negotiation_uuid);

create table cse_case_notes
(
    case_notes_id int auto_increment
        primary key,
    case_notes_uuid varchar(15) charset latin1 not null,
    case_uuid varchar(15) charset latin1 not null,
    notes_uuid varchar(255) charset latin1 not null,
    attribute varchar(255) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_case_notes (attribute);

create index case_uuid
    on cse_case_notes (case_uuid);

create index customer_id
    on cse_case_notes (customer_id);

create index deleted
    on cse_case_notes (deleted);

create index notes_uuid
    on cse_case_notes (notes_uuid);

create table cse_case_person
(
    case_person_id int auto_increment
        primary key,
    case_person_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    person_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_person (case_uuid);

create index deleted
    on cse_case_person (deleted);

create index person_uuid
    on cse_case_person (person_uuid);

create table cse_case_rate
(
    case_rate_id int auto_increment
        primary key,
    case_rate_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    rate_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_case_rate (attribute);

create index case_uuid
    on cse_case_rate (case_uuid);

create index customer_id
    on cse_case_rate (customer_id);

create index deleted
    on cse_case_rate (deleted);

create index rate_uuid
    on cse_case_rate (rate_uuid);

create table cse_case_task
(
    case_task_id int auto_increment
        primary key,
    case_task_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    task_uuid varchar(55) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_task (case_uuid);

create index task_uuid
    on cse_case_task (task_uuid);

create index user_uuid
    on cse_case_task (last_update_user);

create table cse_case_track
(
    case_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    case_id int not null,
    case_uuid varchar(15) not null,
    case_number varchar(255) null,
    file_number varchar(255) default '' null,
    cpointer float default 0 not null,
    adj_number varchar(255) default '' not null,
    case_date date default '0000-00-00' null,
    filing_date date default '0000-00-00' null,
    case_type varchar(255) null,
    venue varchar(255) null,
    case_status varchar(255) null,
    case_substatus varchar(255) not null,
    case_subsubstatus varchar(255) default '' null,
    rating enum('A', 'B', 'C', 'D', 'F', '') default '' not null,
    submittedOn datetime default '0000-00-00 00:00:00' not null,
    supervising_attorney varchar(75) default '' null,
    attorney varchar(75) not null,
    worker varchar(75) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    closed enum('Y', 'N') default 'N' null,
    interpreter_needed enum('Y', 'N') default 'N' null,
    file_location varchar(45) default '' null,
    case_language varchar(100) default '' null,
    lien_filed enum('Y', 'N') default 'N' not null,
    sub_in enum('Y', 'N') default 'N' null,
    special_instructions varchar(1055) default '' null,
    case_description varchar(1055) default '' null comment 'keep new fields describing the case in json format'
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_id
    on cse_case_track (case_id);

create index case_uuid
    on cse_case_track (case_uuid);

create index operation
    on cse_case_track (operation);

create index user_uuid
    on cse_case_track (user_uuid);

create table cse_case_trigger
(
    case_trigger_id int auto_increment
        primary key,
    case_trigger_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    trigger_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(75) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_case_trigger (deleted);

create table cse_case_venue
(
    case_venue_id int auto_increment
        primary key,
    case_venue_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    venue_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index case_uuid
    on cse_case_venue (case_uuid);

create index deleted
    on cse_case_venue (deleted);

create index venue_uuid
    on cse_case_venue (venue_uuid);

create table cse_casestatus
(
    casestatus_id int auto_increment
        primary key,
    casestatus_uuid varchar(15) not null,
    casestatus varchar(255) not null,
    law varchar(45) default 'wcab' null,
    last_change_user varchar(45) default '' null,
    last_change_date datetime default '0000-00-00 00:00:00' null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_casesubstatus
(
    casesubstatus_id int auto_increment
        primary key,
    casesubstatus_uuid varchar(15) not null,
    casesubstatus varchar(255) not null,
    law varchar(45) default 'wcab' null,
    abbr varchar(45) default '' null,
    last_change_user varchar(45) default '' null,
    last_change_date datetime default '0000-00-00 00:00:00' null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_casesubsubstatus
(
    casesubsubstatus_id int auto_increment
        primary key,
    casesubsubstatus_uuid varchar(15) not null,
    casesubsubstatus varchar(255) not null,
    law varchar(45) default 'wcab' null,
    abbr varchar(45) default '' null,
    last_change_user varchar(45) default '' null,
    last_change_date datetime default '0000-00-00 00:00:00' null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_chat
(
    chat_id int auto_increment
        primary key,
    chat_uuid varchar(15) default '' not null,
    dateandtime datetime default '0000-00-00 00:00:00' not null,
    `from` varchar(255) default '' not null,
    chat_to varchar(255) default '' not null,
    chat text not null,
    subject varchar(255) default '' not null,
    attachments varchar(255) default '' not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_chat_user
(
    chat_user_id int auto_increment
        primary key,
    chat_user_uuid varchar(15) not null,
    chat_uuid varchar(15) default '' not null,
    user_uuid varchar(15) default '' not null,
    type enum('from', 'to', 'cc', 'bcc') default 'to' not null,
    thread_uuid varchar(15) default '' not null,
    read_status enum('Y', 'N') default 'N' not null,
    read_date datetime default '0000-00-00 00:00:00' not null,
    action enum('reply', 'forward', '') default '' not null,
    last_updated_date varchar(255) default '' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index chat_uuid
    on cse_chat_user (chat_uuid);

create index user_uuid
    on cse_chat_user (user_uuid);

create table cse_check
(
    check_id int auto_increment
        primary key,
    check_uuid varchar(55) default '' not null,
    parent_check_uuid varchar(55) default '' null,
    method varchar(25) default 'check' null,
    check_number varchar(20) default '' not null,
    check_date date default '0000-00-00' null,
    check_type varchar(50) default 'check' not null,
    check_status enum('P', 'R', 'S', 'V', 'C', '') default '' null comment 'P = Pending
A = Received
S = Sent
V = Void
C = Cleared',
    ledger enum('IN', 'OUT', 'DIS') default 'OUT' null comment 'IN = received
OUT = paid
DIS = disbursement',
    name varchar(30) default '' not null,
    amount_due decimal(10,2) default 0.00 not null,
    payment decimal(10,2) default 0.00 null,
    adjustment decimal(10,2) default 0.00 null,
    balance decimal(10,2) default 0.00 null,
    transaction_date date default '0000-00-00' null,
    memo varchar(255) null,
    carrier_uuid varchar(55) default '' null comment 'this should really be called invoiced_uuid, because other partie types can be invoiced',
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    comment 'cse_check written for Kase' engine=MyISAM charset=latin1;

create index carrier_uuid
    on cse_check (carrier_uuid);

create index check_uuid
    on cse_check (check_uuid);

create index deleted
    on cse_check (deleted);

create index parent_check_uuid
    on cse_check (parent_check_uuid);

create table cse_check_document
(
    check_document_id int auto_increment
        primary key,
    check_document_uuid varchar(15) not null,
    check_uuid varchar(255) charset latin1 default '' not null,
    document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) default '' not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index check_uuid
    on cse_check_document (check_uuid);

create index document_uuid
    on cse_check_document (document_uuid);

create table cse_check_track
(
    check_track_id int auto_increment
        primary key,
    user_uuid varchar(45) collate utf8_unicode_ci not null,
    user_logon varchar(30) default '' not null,
    operation varchar(30) default '' not null,
    time_stamp varchar(30) default '' not null,
    check_id int default 0 not null,
    check_uuid varchar(55) default '' not null,
    method varchar(25) default 'check' null,
    check_number varchar(20) default '' not null,
    check_date date default '0000-00-00' null,
    check_type varchar(50) default 'check' not null,
    check_status enum('P', 'R', 'S', 'V', 'C', '') default '' null comment 'P = Pending
A = Received
S = Sent
V = Void
C = Cleared',
    ledger enum('IN', 'OUT', 'DIS') default 'OUT' null comment 'IN = received
OUT = paid
DIS = disbursement',
    name varchar(30) default '' not null,
    amount_due decimal(7,2) default 0.00 not null,
    payment decimal(7,2) default 0.00 null,
    balance decimal(7,2) default 0.00 null,
    transaction_date varchar(30) null,
    memo varchar(255) null,
    carrier_uuid varchar(55) default '' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    comment 'tracking cse_check written for Kase' engine=MyISAM charset=latin1;

create index check_id
    on cse_check_track (check_id);

create index check_uuid
    on cse_check_track (check_uuid);

create index operation
    on cse_check_track (operation);

create index user_uuid
    on cse_check_track (user_uuid);

create table cse_checkrequest
(
    checkrequest_id int auto_increment
        primary key,
    checkrequest_uuid varchar(25) default '' null,
    check_uuid varchar(15) default '' null,
    requested_by varchar(155) default '' null comment 'user_uuid of requestor',
    payable_to varchar(145) default '' null,
    payable_type enum('C', 'P', 'F') default 'C' null comment 'C = Corporation
P = Person
F = Firm',
    rush_request enum('Y', 'N') default 'N' null,
    request_date datetime default '0000-00-00 00:00:00' null,
    amount decimal(10,2) default 0.00 null,
    needed_date date default '0000-00-00' null,
    reason text null,
    reviewed_by varchar(145) default '' null,
    review_date datetime default '0000-00-00 00:00:00' null,
    approved enum('Y', 'P', 'N', 'V') default 'P' null comment 'P = Pending
V = Void',
    check_number varchar(255) default '' null,
    rejection_reason varchar(1055) default '' null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    comment 'manage requests for checks from hr/accounting dept' collate=utf8_unicode_ci;

create index check_uuid
    on cse_checkrequest (check_uuid);

create index checkrequest_uuid
    on cse_checkrequest (checkrequest_uuid);

create index customer_id
    on cse_checkrequest (customer_id);

create index deleted
    on cse_checkrequest (deleted);

create index requested_by
    on cse_checkrequest (requested_by);

create table cse_checkrequest_track
(
    checkrequest_track_id int auto_increment
        primary key,
    user_uuid varchar(45) collate utf8_unicode_ci not null,
    user_logon varchar(30) default '' not null,
    operation varchar(30) default '' not null,
    time_stamp varchar(30) default '' not null,
    checkrequest_id int default 0 not null,
    checkrequest_uuid varchar(25) collate utf8_unicode_ci default '' null,
    check_uuid varchar(15) collate utf8_unicode_ci default '' null,
    requested_by varchar(155) collate utf8_unicode_ci default '' null comment 'user_uuid of requestor',
    payable_to varchar(145) collate utf8_unicode_ci default '' null,
    payable_type enum('C', 'P', 'F') collate utf8_unicode_ci default 'C' null comment 'C = Corporation
P = Person
F = Firm',
    rush_request enum('Y', 'N') collate utf8_unicode_ci default 'N' null,
    request_date datetime default '0000-00-00 00:00:00' null,
    amount decimal(10,2) default 0.00 null,
    needed_date date default '0000-00-00' null,
    reason text collate utf8_unicode_ci null,
    reviewed_by varchar(145) collate utf8_unicode_ci default '' null,
    review_date datetime default '0000-00-00 00:00:00' null,
    approved enum('Y', 'P', 'N', 'V') collate utf8_unicode_ci default 'P' null comment 'P = Pending
V = Void',
    check_number varchar(255) collate utf8_unicode_ci default '' null,
    rejection_reason varchar(1055) collate utf8_unicode_ci default '' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    comment 'tracking cse_checkrequest written for Kase' engine=MyISAM charset=latin1;

create index checkrequest_id
    on cse_checkrequest_track (checkrequest_id);

create index checkrequest_uuid
    on cse_checkrequest_track (checkrequest_uuid);

create index operation
    on cse_checkrequest_track (operation);

create index user_uuid
    on cse_checkrequest_track (user_uuid);

create table cse_checkrequest_type
(
    checkrequest_type_id int auto_increment
        primary key,
    checkrequest_type varchar(255) default '' null,
    last_change_user varchar(45) default '' null,
    last_change_date datetime default '0000-00-00 00:00:00' null,
    deleted enum('Y', 'N') default 'N' null
);

create table cse_civil
(
    civil_id int auto_increment
        primary key,
    civil_uuid varchar(15) null,
    civil_info varchar(2000) null,
    case_id int null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id varchar(45) null,
    civil_defendant varchar(2000) null
);

create table cse_claim
(
    claim_id int auto_increment
        primary key,
    case_uuid varchar(15) default '' null,
    claim_info text charset utf8 null comment 'data as json',
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    comment 'ssn claim info' collate=utf8_unicode_ci;

create index case_uuid
    on cse_claim (case_uuid);

create table cse_coa
(
    coa_id int auto_increment
        primary key,
    coa_uuid varchar(15) collate utf8_unicode_ci default '' null,
    coa_date datetime default '0000-00-00 00:00:00' null,
    coa_description text null,
    coa_info text null,
    coa_details text null,
    coa_other_details text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null,
    case_id int null,
    coa_new_legal_id int null
);

create table cse_coa_track
(
    coa_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    coa_id int not null,
    coa_date datetime default '0000-00-00 00:00:00' null,
    coa_description text null,
    coa_info text null,
    coa_details text null,
    coa_other_details text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null,
    case_id int null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_contact
(
    contact_id int auto_increment
        primary key,
    contact_uuid varchar(15) default '' null,
    user_uuid varchar(15) default '' null comment 'user id who sent messge to this email address',
    email varchar(255) charset utf8 default '' null,
    spam_status enum('OK', 'BLOCKED') charset utf8 default 'OK' null,
    first_name varchar(255) charset utf8 default '' null,
    last_name varchar(255) charset utf8 default '' null,
    phone varchar(45) charset utf8 default '' null,
    full_address varchar(255) charset utf8 default '' null,
    notes text charset utf8 null,
    customer_id int null,
    deleted enum('Y', 'N') charset utf8 default 'N' null
)
    comment 'email addresses contacted by each user' collate=utf8_unicode_ci;

create index contact_uuid
    on cse_contact (contact_uuid);

create index customer_id
    on cse_contact (customer_id);

create index deleted
    on cse_contact (deleted);

create index email
    on cse_contact (email);

create index user_uuid
    on cse_contact (user_uuid);

create table cse_contact_track
(
    contact_track_id int auto_increment
        primary key,
    track_user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    contact_id int not null,
    contact_uuid varchar(15) default '' null,
    user_uuid varchar(45) not null,
    email varchar(255) default '' null,
    spam_status enum('OK', 'BLOCKED') default 'OK' null,
    first_name varchar(255) default '' null,
    last_name varchar(255) default '' null,
    phone varchar(45) default '' null,
    full_address varchar(255) default '' null,
    customer_id int null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index contact_id
    on cse_contact_track (contact_id);

create index contact_uuid
    on cse_contact_track (contact_uuid);

create index operation
    on cse_contact_track (operation);

create table cse_corporation
(
    corporation_id int auto_increment
        primary key,
    corporation_uuid varchar(15) not null,
    parent_corporation_uuid varchar(15) not null,
    full_name varchar(150) default '' not null,
    company_name varchar(255) default '' null,
    type varchar(255) default '' not null,
    first_name varchar(100) default '' not null,
    last_name varchar(100) default '' not null,
    aka varchar(50) default '' not null,
    preferred_name varchar(100) default '' null,
    employee_phone varchar(100) default '' not null,
    employee_cell varchar(100) default '' null,
    employee_fax varchar(30) default '' not null,
    employee_email varchar(255) default '' not null,
    full_address varchar(255) default '' null,
    additional_addresses varchar(1055) default '' null,
    longitude decimal(9,2) default 0.00 not null,
    latitude decimal(9,2) default 0.00 not null,
    street varchar(255) null,
    city varchar(100) null,
    state char(2) null,
    zip varchar(10) null,
    suite varchar(255) default '' not null,
    company_site varchar(255) default '' not null,
    phone varchar(255) null,
    email varchar(255) null,
    fax varchar(255) null,
    ssn varchar(15) default '' not null,
    dob varchar(15) default '' not null,
    salutation varchar(100) default '' null,
    copying_instructions text not null comment 'codes|other|anyall|special',
    last_updated_date varchar(255) default '' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null,
    phone_ext varchar(45) null,
    comments text null,
    fee varchar(45) null,
    report_number varchar(45) null,
    officer varchar(45) null,
    date varchar(45) null,
    party_type_option varchar(255) default '' null,
    party_representing_id varchar(45) null,
    party_representing_name varchar(45) null,
    party_defendant_option varchar(250) null,
    kai_info text null,
    constraint corporation_uuid
        unique (corporation_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create index company_name
    on cse_corporation (company_name);

create index parent_corporation_uuid
    on cse_corporation (parent_corporation_uuid);

create index type
    on cse_corporation (type);

create table cse_corporation_address
(
    corporation_address_id int auto_increment
        primary key,
    corporation_address_uuid varchar(15) not null,
    corporation_uuid varchar(15) not null,
    address_uuid varchar(15) not null,
    attribute_1 varchar(20) not null,
    attribute_2 varchar(20) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint corporation_address_uuid
        unique (corporation_address_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_corporation_adhoc
(
    adhoc_id int auto_increment
        primary key,
    adhoc_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    corporation_uuid varchar(15) not null,
    adhoc varchar(50) not null,
    adhoc_value varchar(255) not null,
    customer_id int not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index adhoc_uuid
    on cse_corporation_adhoc (adhoc_uuid);

create index case_uuid
    on cse_corporation_adhoc (case_uuid);

create index corporation_uuid
    on cse_corporation_adhoc (corporation_uuid);

create table cse_corporation_adhoc_track
(
    adhoc_track_id int auto_increment
        primary key,
    user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    adhoc_id int not null,
    adhoc_uuid varchar(15) not null,
    case_uuid varchar(15) not null,
    corporation_uuid varchar(15) not null,
    adhoc varchar(50) not null,
    adhoc_value varchar(255) not null,
    customer_id int not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_corporation_check
(
    corporation_check_id int auto_increment
        primary key,
    corporation_check_uuid varchar(15) charset latin1 not null,
    corporation_uuid varchar(15) charset latin1 not null,
    check_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index check_uuid
    on cse_corporation_check (check_uuid);

create index corporation_uuid
    on cse_corporation_check (corporation_uuid);

create table cse_corporation_checkrequest
(
    corporation_checkrequest_id int auto_increment
        primary key,
    corporation_checkrequest_uuid varchar(15) charset latin1 not null,
    corporation_uuid varchar(15) charset latin1 not null,
    checkrequest_uuid varchar(25) default '' null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index checkrequest_uuid
    on cse_corporation_checkrequest (checkrequest_uuid);

create index corporation_uuid
    on cse_corporation_checkrequest (corporation_uuid);

create table cse_corporation_corporation
(
    corporation_corporation_id int auto_increment
        primary key,
    corporation_corporation_uuid varchar(15) not null,
    parent_uuid varchar(15) not null,
    child_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index child_uuid
    on cse_corporation_corporation (child_uuid);

create index parent_uuid
    on cse_corporation_corporation (parent_uuid);

create table cse_corporation_document
(
    corporation_document_id int auto_increment
        primary key,
    corporation_document_uuid varchar(15) not null,
    corporation_uuid varchar(15) not null,
    document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    customer_id int not null,
    `delete` enum('0', '1') default '0' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_corporation_document (corporation_uuid);

create index document_uuid
    on cse_corporation_document (document_uuid);

create table cse_corporation_exam
(
    corporation_exam_id int auto_increment
        primary key,
    corporation_exam_uuid varchar(15) charset latin1 not null,
    corporation_uuid varchar(15) charset latin1 not null,
    exam_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) charset latin1 default '' not null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_corporation_exam (corporation_uuid);

create index exam_uuid
    on cse_corporation_exam (exam_uuid);

create table cse_corporation_financial
(
    corporation_financial_id int auto_increment
        primary key,
    corporation_financial_uuid varchar(15) charset latin1 not null,
    corporation_uuid varchar(15) charset latin1 not null,
    financial_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_corporation_financial (corporation_uuid);

create index financial_uuid
    on cse_corporation_financial (financial_uuid);

create table cse_corporation_homemedical
(
    corporation_homemedical_id int auto_increment
        primary key,
    corporation_homemedical_uuid varchar(15) charset latin1 not null,
    corporation_uuid varchar(15) charset latin1 not null,
    homemedical_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) charset latin1 default '' not null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_corporation_homemedical (corporation_uuid);

create index homemedical_uuid
    on cse_corporation_homemedical (homemedical_uuid);

create table cse_corporation_kinvoice
(
    corporation_kinvoice_id int auto_increment
        primary key,
    corporation_kinvoice_uuid varchar(15) charset latin1 not null,
    corporation_uuid varchar(15) charset latin1 not null,
    kinvoice_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_corporation_kinvoice (corporation_uuid);

create index kinvoice_uuid
    on cse_corporation_kinvoice (kinvoice_uuid);

create table cse_corporation_ks
(
    corporation_id int auto_increment
        primary key,
    corporation_uuid varchar(15) not null,
    parent_corporation_uuid varchar(15) not null,
    full_name varchar(50) default '' not null,
    company_name varchar(255) default '' null,
    type varchar(255) default '' not null,
    first_name varchar(100) default '' not null,
    last_name varchar(100) default '' not null,
    aka varchar(50) default '' not null,
    preferred_name varchar(100) default '' null,
    employee_phone varchar(100) default '' not null,
    employee_fax varchar(30) default '' not null,
    employee_email varchar(255) default '' not null,
    full_address varchar(255) default '' null,
    longitude decimal(9,2) default 0.00 not null,
    latitude decimal(9,2) default 0.00 not null,
    street varchar(255) null,
    city varchar(100) null,
    state char(2) null,
    zip varchar(10) null,
    suite varchar(255) default '' not null,
    company_site varchar(255) default '' not null,
    phone varchar(20) null,
    email varchar(255) null,
    fax varchar(20) null,
    ssn varchar(15) default '' not null,
    dob varchar(15) default '' not null,
    salutation varchar(100) default '' null,
    copying_instructions varchar(255) default '' not null comment 'codes|other|anyall|special',
    last_updated_date varchar(255) default '' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null,
    constraint corporation_uuid
        unique (corporation_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_corporation_lostincome
(
    corporation_lostincome_id int auto_increment
        primary key,
    corporation_lostincome_uuid varchar(15) not null,
    corporation_uuid varchar(15) not null,
    lostincome_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_corporation_lostincome (corporation_uuid);

create index deleted
    on cse_corporation_lostincome (deleted);

create index lostincome_uuid
    on cse_corporation_lostincome (lostincome_uuid);

create table cse_corporation_matrixrequest
(
    corporation_matrixrequest_id int auto_increment
        primary key,
    corporation_id int null,
    request_id int null comment 'request id from matrix_national',
    request_date timestamp default CURRENT_TIMESTAMP null,
    request_by varchar(4) null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
);

create table cse_corporation_negotiation
(
    corporation_negotiation_id int auto_increment
        primary key,
    corporation_negotiation_uuid varchar(15) charset latin1 not null,
    corporation_uuid varchar(15) charset latin1 not null,
    negotiation_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_corporation_negotiation (corporation_uuid);

create index negotiation_uuid
    on cse_corporation_negotiation (negotiation_uuid);

create table cse_corporation_notes
(
    corporation_notes_id int auto_increment
        primary key,
    corporation_notes_uuid varchar(15) charset latin1 not null,
    corporation_uuid varchar(15) charset latin1 not null,
    notes_uuid varchar(255) charset latin1 default '' not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_corporation_notes (corporation_uuid);

create index notes_uuid
    on cse_corporation_notes (notes_uuid);

create table cse_corporation_track
(
    corporation_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    corporation_id int not null,
    corporation_uuid varchar(15) not null,
    parent_corporation_uuid varchar(15) not null,
    full_name varchar(50) not null,
    company_name varchar(255) null,
    type varchar(255) not null,
    first_name varchar(100) not null,
    last_name varchar(100) not null,
    aka varchar(50) not null,
    preferred_name varchar(100) null,
    employee_phone varchar(100) not null,
    employee_cell varchar(100) default '' null,
    employee_fax varchar(30) not null,
    employee_email varchar(255) default '' not null,
    full_address varchar(255) null,
    additional_addresses varchar(1055) default '' null,
    longitude decimal(9,2) default 0.00 not null,
    latitude decimal(9,2) default 0.00 not null,
    street varchar(255) null,
    city varchar(100) null,
    state char(2) null,
    zip varchar(10) null,
    suite varchar(255) not null,
    company_site varchar(255) not null,
    phone varchar(255) null,
    email varchar(255) null,
    fax varchar(255) null,
    ssn varchar(15) not null,
    dob varchar(15) not null,
    salutation varchar(100) null,
    copying_instructions varchar(255) default '' not null comment 'codes|other|anyall|special',
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null,
    phone_ext varchar(45) null,
    comments varchar(45) null,
    fee varchar(45) null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_cost_type
(
    cost_type_id int auto_increment
        primary key,
    cost_type varchar(255) default '' null,
    last_change_user varchar(45) default '' null,
    last_change_date datetime default '0000-00-00 00:00:00' null,
    deleted enum('Y', 'N') default 'N' null
);

create table cse_courtcalendar
(
    courtcalendar_id int auto_increment
        primary key,
    office varchar(45) default '' null,
    judge_name varchar(45) default '' null,
    worker_name varchar(45) default '' null,
    legacy_case_number varchar(45) default '' null,
    case_number varchar(45) default '' null,
    hearing_type varchar(45) default '' null,
    applicant_law_firm varchar(1055) default '' null,
    defense_law_firm varchar(1055) default '' null,
    hearing_time datetime default '0000-00-00 00:00:00' null,
    hearing_location varchar(245) default '' null,
    event_uuid varchar(145) default '' null,
    case_uuid varchar(45) default '' null,
    import_date datetime default '0000-00-00 00:00:00' null,
    customer_id int default 0 null
)
    collate=utf8_unicode_ci;

create index adj_number
    on cse_courtcalendar (case_number);

create index applicant_law_firm
    on cse_courtcalendar (applicant_law_firm);

create index case_uuid
    on cse_courtcalendar (case_uuid);

create index customer_id
    on cse_courtcalendar (customer_id);

create index defense_law_firm
    on cse_courtcalendar (defense_law_firm);

create index event_uuid
    on cse_courtcalendar (event_uuid);

create index hearing_time
    on cse_courtcalendar (hearing_time);

create index import_date
    on cse_courtcalendar (import_date);

create index office
    on cse_courtcalendar (office);

create index worker_name
    on cse_courtcalendar (worker_name);

create table cse_customer
(
    customer_id int(8) unsigned auto_increment
        primary key,
    deleted enum('Y', 'N') default 'N' not null,
    customer_uuid varchar(15) default '' not null,
    parent_customer_id int default 0 not null,
    eams_no varchar(10) default '' not null,
    cus_type varchar(100) default 'Legal Firm' not null,
    data_source varchar(20) null comment 'customer dbf data source',
    data_path varchar(255) null,
    archive_path varchar(255) default '' null,
    import_db_source varchar(255) default '' null comment 'import source DB name',
    start_date date default '0000-00-00' null,
    cus_name varchar(100) default '' not null,
    letter_name varchar(255) default '' null,
    cus_name_first varchar(100) null,
    cus_name_middle varchar(100) null,
    cus_name_last varchar(100) null,
    cus_street varchar(100) null,
    cus_city varchar(100) null,
    cus_state varchar(2) default 'CA' not null,
    cus_zip varchar(5) null,
    cus_county varchar(50) null comment 'filing county',
    cus_ip varchar(255) null,
    cus_fedtax_id varchar(20) default '' not null comment ' tax_id for actual customer tax id',
    cus_uan varchar(255) default '' not null,
    cus_barnumber varchar(255) default '' null,
    last_on date null,
    cus_ok char default '0' not null,
    cus_email varchar(75) null,
    cus_phone varchar(50) null,
    cus_fax varchar(50) null,
    notes text null,
    password varchar(45) default '' not null,
    pwd varchar(255) null,
    admin_client varchar(7) default '8894723' not null,
    session_id varchar(30) null,
    dateandtime date default '0000-00-00' not null,
    ip_address varchar(50) default '0.0.0.0' not null,
    xl_filed enum('Y', 'N') default 'N' not null comment 'filed with eams in xl sheet',
    inhouse_id int default 0 not null,
    jetfile_id int default 0 null,
    permissions varchar(55) default 'rwei' not null comment 'read / write / export / import',
    ddl_venue varchar(3) null comment 'default venue',
    office_manager_first varchar(50) charset utf8 default '' not null,
    office_manager_last varchar(50) charset utf8 default '' not null,
    office_manager_middle varchar(50) charset utf8 default '' not null,
    office_manager_phone varchar(50) charset utf8 default '' not null,
    office_manager_email varchar(50) charset utf8 default '' not null,
    user_rate decimal(7,2) default 0.00 null comment 'per year',
    corporation_rate decimal(7,2) default 0.00 null,
    constraint customer_uuid
        unique (customer_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create index eams_no
    on cse_customer (eams_no);

create table cse_customer_address
(
    customer_address_id int auto_increment
        primary key,
    customer_address_uuid varchar(15) not null,
    customer_uuid int not null,
    address_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_customer varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint customer_address_uuid
        unique (customer_address_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_customer_contact
(
    customer_contact_id int auto_increment
        primary key,
    customer_contact_uuid varchar(15) not null,
    customer_id int not null,
    customer_uuid varchar(15) not null,
    contact_id int not null,
    contact_uuid varchar(15) not null,
    attribute_1 varchar(15) not null,
    attribute_2 varchar(15) not null,
    last_updated_date varchar(50) not null,
    last_update_user varchar(50) not null,
    `delete` enum('0', '1') default '0' not null,
    constraint customer_contact_uuid
        unique (customer_contact_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_customer_corporation
(
    customer_corporation_id int auto_increment
        primary key,
    customer_corporation_uuid varchar(15) not null,
    customer_uuid int not null,
    corporation_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_customer varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint customer_corporation_uuid
        unique (customer_corporation_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_customer_document
(
    customer_document_id int auto_increment
        primary key,
    customer_document_uuid varchar(15) not null,
    customer_uuid int not null,
    document_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_customer varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint customer_document_uuid
        unique (customer_document_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_customer_document_filters
(
    filter_id int auto_increment
        primary key,
    document_filters text null,
    customer_id int default 0 null
);

create table cse_customer_event
(
    customer_event_id int auto_increment
        primary key,
    customer_event_uuid varchar(15) not null,
    customer_uuid int not null,
    event_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_customer varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint customer_event_uuid
        unique (customer_event_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_customer_ipaddress
(
    customer_ipaddress_id int auto_increment
        primary key,
    customer_ipaddress_uuid varchar(15) charset latin1 not null,
    customer_uuid varchar(15) charset latin1 not null,
    ipaddress_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) collate utf8_unicode_ci not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM;

create index customer_uuid
    on cse_customer_ipaddress (customer_uuid);

create index ipaddress_uuid
    on cse_customer_ipaddress (ipaddress_uuid);

create table cse_customer_notes
(
    customer_notes_id int auto_increment
        primary key,
    customer_notes_uuid varchar(15) not null,
    customer_uuid varchar(155) default '' not null,
    notes_uuid varchar(155) default '' not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int default 0 not null,
    constraint customer_note_uuid
        unique (customer_notes_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_customer_person
(
    customer_person_id int auto_increment
        primary key,
    customer_person_uuid varchar(15) not null,
    customer_uuid int not null,
    person_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_customer varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint customer_person_uuid
        unique (customer_person_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_customer_reports
(
    customer_report_id int auto_increment
        primary key,
    customer_id int default 0 null,
    user_id int default 0 null,
    report varchar(45) charset utf8 default '' null,
    api varchar(255) default '' null,
    deleted enum('Y', 'N') charset utf8 default 'N' null
)
    collate=utf8_unicode_ci;

create index customer_id
    on cse_customer_reports (customer_id);

create index user_id
    on cse_customer_reports (user_id);

create table cse_customer_setting
(
    setting_id int auto_increment
        primary key,
    setting_uuid varchar(15) not null,
    customer_uuid varchar(15) not null,
    category varchar(100) not null,
    setting varchar(50) not null,
    setting_value varchar(255) not null,
    setting_type varchar(255) not null comment 'color, date, choice, etc...',
    default_value varchar(255) not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_uuid
    on cse_customer_setting (customer_uuid);

create index setting_uuid
    on cse_customer_setting (setting_uuid);

create table cse_customer_track
(
    customer_track_id int auto_increment
        primary key,
    customer_id int not null,
    customer_uuid varchar(15) charset latin1 default '' not null,
    operation varchar(30) charset latin1 default '' not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null,
    description text charset latin1 not null,
    description_html text charset latin1 null comment 'html version of letters',
    verified enum('Y', 'N') charset latin1 default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_deduction
(
    deduction_id int auto_increment
        primary key,
    deduction_uuid varchar(15) default '' null,
    deduction_date date default '0000-00-00' null,
    tracking_number varchar(145) default '' null,
    deduction_description varchar(1055) charset utf8 default '' null,
    amount decimal(11,2) default 0.00 null,
    payment decimal(11,2) default 0.00 null,
    adjustment decimal(11,2) default 0.00 null,
    balance decimal(11,2) default 0.00 null,
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    collate=utf8_unicode_ci;

create index customer_id
    on cse_deduction (customer_id);

create index deduction_uuid
    on cse_deduction (deduction_uuid);

create index deleted
    on cse_deduction (deleted);

create table cse_deduction_track
(
    deduction_track_id int auto_increment
        primary key,
    user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deduction_id int not null,
    deduction_uuid varchar(15) default '' null,
    deduction_date date default '0000-00-00' null,
    tracking_number varchar(145) default '' null,
    deduction_description varchar(1055) default '' null,
    amount decimal(11,2) default 0.00 null,
    payment decimal(11,2) default 0.00 null,
    adjustment decimal(11,2) default 0.00 null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_deduction_track (customer_id);

create index deduction_id
    on cse_deduction_track (deduction_id);

create index operation
    on cse_deduction_track (operation);

create index user_uuid
    on cse_deduction_track (user_uuid);

create table cse_disability
(
    disability_id int auto_increment
        primary key,
    disability_uuid varchar(45) default '' null,
    claim text charset utf8 null,
    description text charset utf8 null,
    ailment varchar(255) charset utf8 default '' null,
    severity varchar(45) charset utf8 default '' null,
    duration varchar(255) default '' null,
    duty varchar(255) charset utf8 default '' null,
    limits varchar(255) charset utf8 default '' null,
    treatment varchar(255) charset utf8 default '' null,
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index disability_uuid
    on cse_disability (disability_uuid);

create table cse_disability_track
(
    disability_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP null on update CURRENT_TIMESTAMP,
    disability_id int not null,
    disability_uuid varchar(45) default '' null,
    claim text charset utf8 null,
    description text charset utf8 null,
    ailment varchar(255) charset utf8 default '' null,
    severity varchar(45) charset utf8 default '' null,
    duration varchar(255) default '' null,
    duty varchar(255) charset utf8 default '' null,
    limits varchar(255) charset utf8 default '' null,
    treatment varchar(255) charset utf8 default '' null,
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index disability_uuid
    on cse_disability_track (disability_uuid);

create index operation
    on cse_disability_track (operation);

create table cse_document
(
    document_id int auto_increment
        primary key,
    document_uuid varchar(15) charset latin1 default '' not null,
    parent_document_uuid varchar(15) charset latin1 default '' not null,
    document_name varchar(255) charset latin1 default '' not null,
    document_date datetime default '0000-00-00 00:00:00' not null,
    document_filename varchar(255) charset latin1 default '' not null,
    document_extension varchar(255) charset latin1 default '' not null,
    thumbnail_folder varchar(255) default '' not null,
    description text charset latin1 not null,
    description_html text charset latin1 null comment 'html version of letters',
    source varchar(255) default '' not null,
    received_date datetime default '0000-00-00 00:00:00' not null,
    type varchar(100) charset latin1 default '' not null,
    verified enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null,
    deleted enum('N', 'Y') charset latin1 default 'N' not null,
    constraint document_uuid
        unique (document_uuid)
)
    comment 'documents' engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_document (customer_id);

create index deleted
    on cse_document (deleted);

create index document_filename
    on cse_document (document_filename);

create index parent_document_uuid
    on cse_document (parent_document_uuid);

create table cse_document_kinvoice
(
    document_kinvoice_id int auto_increment
        primary key,
    document_kinvoice_uuid varchar(15) not null,
    document_uuid varchar(15) not null,
    kinvoice_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_document_kinvoice (attribute);

create index customer_id
    on cse_document_kinvoice (customer_id);

create index deleted
    on cse_document_kinvoice (deleted);

create index document_uuid
    on cse_document_kinvoice (document_uuid);

create index kinvoice_uuid
    on cse_document_kinvoice (kinvoice_uuid);

create table cse_document_titles
(
    product_delivery varchar(12) null,
    document_type varchar(15) null,
    document_title varchar(69) null,
    active enum('Y', 'N') default 'Y' not null
)
    engine=MyISAM;

create table cse_document_track
(
    document_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP null on update CURRENT_TIMESTAMP,
    document_id int not null,
    document_uuid varchar(15) default '' not null,
    parent_document_uuid varchar(15) default '' not null,
    document_name varchar(255) default '' not null,
    document_date datetime default '0000-00-00 00:00:00' not null,
    document_filename varchar(255) default '' not null,
    document_extension varchar(255) default '' not null,
    thumbnail_folder varchar(255) default '' not null,
    description text not null,
    description_html text null comment 'html version of letters',
    type varchar(100) default '' not null,
    verified enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    deleted enum('N', 'Y') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index document_filename
    on cse_document_track (document_filename);

create index document_uuid
    on cse_document_track (document_uuid);

create index operation
    on cse_document_track (operation);

create table cse_document_type
(
    document_type_id int auto_increment
        primary key,
    customer_id int not null,
    name varchar(100) not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_downloads
(
    downloads_id int auto_increment
        primary key,
    downloadkey varchar(32) not null,
    file varchar(255) default '' not null,
    injury_id int not null,
    sent_by varchar(100) not null,
    downloads int unsigned default 0 not null,
    expires datetime default '0000-00-00 00:00:00' not null,
    customer_id int unsigned default 0 not null,
    constraint downloadkey
        unique (downloadkey)
)
    engine=MyISAM charset=latin1;

create table cse_eams_carriers
(
    carrier_id int auto_increment
        primary key,
    carrier_uuid char(8) charset latin1 not null,
    eams_ref_number char(8) charset latin1 not null,
    firm_name text charset latin1 not null,
    street_1 varchar(255) charset latin1 not null,
    street_2 varchar(255) default '' not null,
    city varchar(255) charset latin1 not null,
    state char(2) charset latin1 not null,
    zip_code char(5) charset latin1 not null,
    phone varchar(20) charset latin1 not null,
    service_method varchar(255) charset latin1 not null,
    last_update datetime not null,
    active enum('Y', 'N') default 'Y' null,
    last_import_date datetime default '0000-00-00 00:00:00' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index street_1
    on cse_eams_carriers (street_1);

create index zip_code
    on cse_eams_carriers (zip_code);

create table cse_eams_claimants
(
    claimant_id int auto_increment
        primary key,
    claimant_uuid char(8) default '' null,
    eams_ref_number char(8) not null,
    firm_name text not null,
    street_1 varchar(255) not null,
    street_2 varchar(255) default '' not null,
    city varchar(255) not null,
    state char(2) not null,
    zip_code char(5) not null,
    phone varchar(20) not null,
    service_method varchar(255) null,
    last_update datetime not null,
    active enum('Y', 'N') default 'Y' null,
    last_import_date datetime default '0000-00-00 00:00:00' null
)
    engine=MyISAM charset=latin1;

create index street_1
    on cse_eams_claimants (street_1);

create index zip_code
    on cse_eams_claimants (zip_code);

create table cse_eams_forms
(
    eams_form_id int auto_increment
        primary key,
    name varchar(255) default '' not null,
    sort_order int default 0 not null,
    display_name varchar(255) not null,
    status enum('ready', 'not ready', 'working - missing fields', 'in progress') default 'ready' not null,
    category varchar(55) default '' null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_eams_forms_track
(
    eams_forms_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    eams_form_id int not null,
    name varchar(255) default '' not null,
    sort_order int default 0 not null,
    display_name varchar(255) not null,
    status enum('ready', 'not ready', 'working - missing fields', 'in progress') default 'ready' not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_eams_reps
(
    rep_id int auto_increment
        primary key,
    rep_uuid char(8) not null,
    eams_ref_number char(8) charset latin1 not null,
    firm_name varchar(1055) charset latin1 not null,
    street_1 varchar(255) charset latin1 not null,
    street_2 varchar(255) default '' not null,
    city varchar(255) charset latin1 not null,
    state char(2) charset latin1 not null,
    zip_code char(5) charset latin1 not null,
    phone varchar(20) charset latin1 not null,
    service_method varchar(255) charset latin1 not null,
    last_update datetime default '0000-00-00 00:00:00' not null,
    active enum('Y', 'N') default 'Y' null,
    last_import_date datetime default '0000-00-00 00:00:00' not null,
    create_date timestamp default CURRENT_TIMESTAMP null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index eams_ref_number
    on cse_eams_reps (eams_ref_number);

create index firm
    on cse_eams_reps (firm_name);

create index street_1
    on cse_eams_reps (street_1);

create index zip_code
    on cse_eams_reps (zip_code);

create table cse_eamsinfo
(
    eamsinfo_id int auto_increment
        primary key,
    eamsinfo_uuid varchar(45) null,
    scrape_date datetime default '0000-00-00 00:00:00' null,
    applicant text null,
    bodyparts text null,
    roles text null,
    parties text null,
    events text null,
    hearings text null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    comment 'store information from eams case search in json form';

create index eamsinfo_uuid
    on cse_eamsinfo (eamsinfo_uuid);

create table cse_email
(
    email_id int auto_increment
        primary key,
    email_uuid varchar(155) default '' not null,
    email_name varchar(155) not null,
    email_method enum('POP3', 'IMAP') default 'POP3' null,
    email_server varchar(155) default '' not null,
    email_port int default 110 null comment 'PORT number',
    certificate enum('Y', 'N') default 'Y' null,
    outgoing_server varchar(100) default '' null,
    outgoing_port int default 25 null,
    encrypted_connection enum('None', 'Auto', 'SSL', 'TLS') default 'None' null,
    ssl_required enum('Y', 'N') default 'N' null,
    email_pwd varchar(255) not null,
    email_address varchar(255) default '' not null,
    email_phone varchar(50) default '' not null,
    cell_carrier varchar(50) default '' not null,
    read_messages enum('Y', 'N') default 'N' null,
    emails_pending enum('Y', 'N') default 'Y' null,
    customer_id int default 0 not null,
    active enum('Y', 'N') default 'Y' null,
    deleted enum('Y', 'N') default 'N' not null,
    constraint email_uuid
        unique (email_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_email_track
(
    email_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    email_id int not null,
    email_uuid varchar(15) not null,
    email_name varchar(155) not null,
    email_server varchar(100) not null,
    ssl_required enum('Y', 'N') default 'N' null,
    email_method enum('POP3', 'IMAP') default 'POP3' null,
    email_port int default 110 null comment 'PORT number',
    email_pwd varchar(255) not null,
    email_address varchar(255) default '' not null,
    email_phone varchar(50) default '' not null,
    cell_carrier varchar(50) default '' not null,
    read_messages enum('Y', 'N') default 'N' null,
    emails_pending enum('Y', 'N') default 'Y' null,
    customer_id int not null,
    active enum('Y', 'N') default 'Y' null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_event
(
    event_id int auto_increment
        primary key,
    event_uuid varchar(155) default '' not null,
    event_name varchar(255) default '' not null,
    event_date varchar(255) default '' not null,
    event_duration int default 60 not null,
    event_description text not null,
    event_first_name varchar(255) default '' not null,
    event_last_name varchar(255) default '' not null,
    event_dateandtime datetime default '0000-00-00 00:00:00' not null,
    event_end_time varchar(255) default '' not null,
    full_address varchar(255) default '' not null,
    judge varchar(95) default '' not null,
    assignee varchar(65) default '' not null,
    event_title varchar(255) default '' not null,
    event_email varchar(255) default '' not null,
    event_hour varchar(255) default '' not null,
    event_type varchar(100) default '' not null,
    event_type_abbr varchar(20) default '' not null comment 'for calendar',
    event_from varchar(250) default '' not null,
    event_priority varchar(100) default '' not null,
    end_date datetime default '0000-00-00 00:00:00' not null,
    completed_date datetime default '0000-00-00 00:00:00' not null,
    callback_date datetime default '0000-00-00 00:00:00' not null,
    callback_completed datetime default '0000-00-00 00:00:00' not null,
    color varchar(50) default 'blue' not null,
    off_calendar enum('Y', 'N') default 'N' null comment 'to mark something off calendar without erasing it from the calendar ',
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null,
    constraint event_uuid
        unique (event_uuid)
)
    comment 'table for event details only' engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_event (customer_id);

create index deleted
    on cse_event (deleted);

create index event_dateandtime
    on cse_event (event_dateandtime);

create index event_title
    on cse_event (event_title);

create index event_type
    on cse_event (event_type);

create table cse_event_address
(
    event_address_id int auto_increment
        primary key,
    event_address_uuid varchar(15) not null,
    event_uuid varchar(15) not null,
    address_uuid varchar(15) not null,
    attribute_1 varchar(20) not null,
    attribute_2 varchar(20) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint event_address_uuid
        unique (event_address_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_event_corporation
(
    event_corporation_id int auto_increment
        primary key,
    event_corporation_uuid varchar(15) not null,
    event_uuid varchar(25) not null,
    person_uuid varchar(25) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index event_uuid
    on cse_event_corporation (event_uuid);

create index person_uuid
    on cse_event_corporation (person_uuid);

create table cse_event_document
(
    event_document_id int auto_increment
        primary key,
    event_document_uuid varchar(15) not null,
    event_id int not null,
    event_uuid varchar(15) not null,
    document_id int not null,
    document_uuid varchar(15) not null,
    parent_document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    customer_id int not null,
    `delete` enum('0', '1') default '0' not null,
    constraint event_document_uuid
        unique (event_document_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_event_message
(
    event_message_id int auto_increment
        primary key,
    event_message_uuid varchar(15) not null,
    event_uuid varchar(15) not null,
    message_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index event_uuid
    on cse_event_message (event_uuid);

create index message_uuid
    on cse_event_message (message_uuid);

create table cse_event_person
(
    event_person_id int auto_increment
        primary key,
    event_person_uuid varchar(15) not null,
    event_uuid varchar(15) not null,
    person_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted varchar(15) default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index event_uuid
    on cse_event_person (event_uuid);

create index person_uuid
    on cse_event_person (person_uuid);

create table cse_event_reminder
(
    event_reminder_id int auto_increment
        primary key,
    event_reminder_uuid varchar(15) not null,
    event_uuid varchar(15) not null,
    reminder_uuid varchar(15) not null,
    attribute_1 varchar(255) default '' not null,
    attribute_2 varchar(255) default '' not null,
    last_updated_date varchar(255) default '' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index event_uuid
    on cse_event_reminder (event_uuid);

create index reminder_uuid
    on cse_event_reminder (reminder_uuid);

create table cse_event_track
(
    event_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    event_id int not null,
    event_uuid varchar(155) default '' not null,
    event_name varchar(255) not null,
    event_date varchar(255) not null,
    event_duration int default 60 not null,
    event_description text not null,
    event_first_name varchar(255) not null,
    event_last_name varchar(255) not null,
    event_dateandtime datetime not null,
    event_end_time varchar(255) not null,
    full_address varchar(255) not null,
    judge varchar(95) default '' not null,
    assignee varchar(65) not null,
    event_title varchar(255) not null,
    event_email varchar(255) not null,
    event_hour varchar(255) not null,
    event_type varchar(100) default '' not null,
    event_type_abbr varchar(20) default '' not null comment 'for calendar',
    event_from varchar(250) not null,
    event_priority varchar(100) not null,
    end_date datetime not null,
    completed_date datetime not null,
    callback_date datetime not null,
    callback_completed datetime not null,
    color varchar(50) default 'blue' not null,
    customer_id int not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index event_id
    on cse_event_track (event_id);

create index operation
    on cse_event_track (operation);

create table cse_event_user
(
    event_user_id int auto_increment
        primary key,
    event_user_uuid varchar(45) not null,
    event_uuid varchar(155) default '' not null,
    thread_uuid varchar(155) default '' null,
    user_uuid varchar(45) not null,
    type enum('from', 'to', 'cc', 'bcc') default 'to' not null,
    read_status enum('Y', 'N') default 'N' not null,
    read_date datetime default '0000-00-00 00:00:00' not null,
    action enum('reply', 'forward') not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index event_uuid
    on cse_event_user (event_uuid);

create index user_uuid
    on cse_event_user (user_uuid);

create table cse_exam
(
    exam_id int auto_increment
        primary key,
    exam_uuid varchar(15) null,
    exam_dateandtime datetime default '0000-00-00 00:00:00' null,
    exam_status varchar(255) default '' null,
    exam_type varchar(45) default '' null,
    specialty varchar(255) default '' null,
    requestor varchar(255) default '' null,
    comments text null,
    permanent_stationary enum('Y', 'N') default 'N' null comment 'â€œPermanent and stationary statusâ€ is the point when the employee has reached maximal medical improvement, meaning his or her condition is well stabilized, and unlikely to change substantially in the next year with or without medical treatment.',
    fs_date date default '0000-00-00' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
);

create index customer_id
    on cse_exam (customer_id);

create index deleted
    on cse_exam (deleted);

create index exam_uuid
    on cse_exam (exam_uuid);

create table cse_exam_document
(
    exam_document_id int auto_increment
        primary key,
    exam_document_uuid varchar(15) not null,
    exam_uuid varchar(255) charset latin1 default '' not null,
    document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) default '' not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index document_uuid
    on cse_exam_document (document_uuid);

create index exam_uuid
    on cse_exam_document (exam_uuid);

create table cse_exam_track
(
    exam_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    exam_id int not null,
    exam_uuid varchar(15) null,
    exam_dateandtime datetime default '0000-00-00 00:00:00' null,
    exam_status varchar(255) default '' null,
    exam_type varchar(45) default '' null,
    specialty varchar(255) default '' null,
    requestor varchar(255) default '' null,
    comments text null,
    permanent_stationary enum('Y', 'N') default 'N' null comment 'â€œPermanent and stationary statusâ€ is the point when the employee has reached maximal medical improvement, meaning his or her condition is well stabilized, and unlikely to change substantially in the next year with or without medical treatment.',
    fs_date date default '0000-00-00' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_fdf_responses
(
    response_id int auto_increment
        primary key,
    response text null,
    customer_id int default 0 null,
    case_id int default 0 null,
    form_name varchar(255) default '' null,
    document_path varchar(255) default '' null,
    pdftk varchar(1055) default '' null,
    dateandtime timestamp default CURRENT_TIMESTAMP null
)
    collate=utf8_unicode_ci;

create table cse_fee
(
    fee_id int auto_increment
        primary key,
    fee_uuid varchar(15) default '' null,
    fee_parent_uuid varchar(15) default '' null,
    fee_type varchar(45) default '' null,
    fee_requested date default '0000-00-00' null,
    fee_date date default '0000-00-00' null,
    fee_billed decimal(11,2) default 0.00 null,
    fee_paid varchar(255) default '0.00' null,
    fee_recipient varchar(255) default '' null,
    fee_memo varchar(1055) default '' null,
    fee_doctor_id int default 0 null,
    fee_check_number varchar(45) default '' null,
    fee_referral varchar(255) default '' null,
    full_name varchar(255) default '' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null,
    paid_fee decimal(11,2) default 0.00 null,
    hourly_rate decimal(11,2) default 0.00 null,
    hours decimal(11,2) default 0.00 null,
    fee_by varchar(4) default '' null,
    constraint fee_uuid
        unique (fee_uuid)
)
    comment 'settlement costs and fees' collate=utf8_unicode_ci;

create index customer_id
    on cse_fee (customer_id);

create index deleted
    on cse_fee (deleted);

create index fee_doctor_id
    on cse_fee (fee_doctor_id);

create table cse_fee_check
(
    fee_check_id int auto_increment
        primary key,
    fee_check_uuid varchar(15) charset latin1 not null,
    fee_uuid varchar(15) charset latin1 not null,
    check_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index check_uuid
    on cse_fee_check (check_uuid);

create index fee_uuid
    on cse_fee_check (fee_uuid);

create table cse_fee_track
(
    fee_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP null,
    fee_id int not null,
    fee_uuid varchar(15) default '' null,
    fee_parent_uuid varchar(15) default '' null,
    fee_type varchar(45) default '' null,
    fee_requested date default '0000-00-00' null,
    fee_date date default '0000-00-00' null,
    fee_billed decimal(11,2) default 0.00 null,
    fee_paid varchar(255) default '0.00' null,
    fee_recipient varchar(255) default '' null,
    fee_memo varchar(1055) default '' null,
    fee_doctor_id int default 0 null,
    fee_check_number varchar(45) default '' null,
    fee_referral varchar(255) default '' null,
    full_name varchar(255) default '' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null,
    paid_fee decimal(11,2) default 0.00 null,
    hourly_rate decimal(11,2) default 0.00 null,
    hours decimal(11,2) default 0.00 null,
    fee_by varchar(4) default '' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index fee_id
    on cse_fee_track (fee_id);

create index operation
    on cse_fee_track (operation);

create index time_stamp
    on cse_fee_track (time_stamp);

create table cse_fee_user
(
    fee_user_id int auto_increment
        primary key,
    fee_user_uuid varchar(15) charset latin1 not null,
    fee_uuid varchar(15) charset latin1 not null,
    user_uuid varchar(45) charset latin1 default '' not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index fee_uuid
    on cse_fee_user (fee_uuid);

create index user_uuid
    on cse_fee_user (user_uuid);

create table cse_filing
(
    injury_filing_id int auto_increment
        primary key,
    injury_id int default 0 null,
    injury_uuid varchar(45) default '' null,
    filing_id int default 0 null,
    customer_id int default 0 null,
    user_id int default 0 null,
    filing_date timestamp null,
    form varchar(45) default '' null,
    deleted enum('Y', 'N') default 'N' null
)
    comment 'filing_id from cajetfile';

create index case_id
    on cse_filing (injury_id);

create table cse_financial
(
    financial_id int auto_increment
        primary key,
    financial_uuid varchar(15) null,
    financial_info varchar(2000) null,
    case_id int null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id varchar(45) null,
    financial_defendant varchar(2000) null
);

create table cse_gmail
(
    gmail_id int auto_increment
        primary key,
    user_id int default 0 null,
    token varchar(255) default '' null,
    token_date timestamp default CURRENT_TIMESTAMP null,
    origin varchar(255) default '' null
)
    comment 'keep track of tokens for users';

create index user_id
    on cse_gmail (user_id);

create table cse_homemedical
(
    homemedical_id int auto_increment
        primary key,
    homemedical_uuid varchar(15) not null,
    recommended_by varchar(255) not null,
    provider_name varchar(255) not null,
    prescription enum('Y', 'N') default 'N' not null,
    homemedical_report enum('Y', 'N') default 'N' not null,
    prescription_date date default '0000-00-00' not null,
    report_date date default '0000-00-00' not null,
    filling_fee_paid_date date default '0000-00-00' not null,
    retainer_date date default '0000-00-00' not null,
    lien_filled_date date default '0000-00-00' not null,
    reviewed_date date default '0000-00-00' not null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_homemedical_track
(
    homemedical_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    homemedical_id int default 0 not null,
    homemedical_uuid varchar(15) not null,
    recommended_by varchar(255) not null,
    provider_name varchar(255) not null,
    prescription enum('Y', 'N') default 'N' not null,
    homemedical_report enum('Y', 'N') default 'N' not null,
    prescription_date date default '0000-00-00' not null,
    report_date date default '0000-00-00' not null,
    filling_fee_paid_date date default '0000-00-00' not null,
    retainer_date date default '0000-00-00' not null,
    lien_filled_date date default '0000-00-00' not null,
    reviewed_date date default '0000-00-00' not null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_injury
(
    injury_id int auto_increment
        primary key,
    injury_uuid varchar(15) not null,
    injury_number smallint default 1 not null,
    adj_number varchar(255) default '' not null,
    type varchar(50) default '' not null,
    injury_status varchar(20) default '' null,
    occupation varchar(255) default '' not null,
    occupation_group varchar(50) default '' null,
    start_date date default '0000-00-00' not null,
    end_date date default '0000-00-00' not null,
    ct_dates_note varchar(255) default '' not null,
    body_parts varchar(255) default '' not null,
    statute_limitation date default '0000-00-00' not null,
    statute_interval int default 730 null comment 'number of days for statute of limitation',
    explanation text not null,
    deu enum('Y', 'N') default 'N' not null,
    full_address varchar(255) default '' not null,
    street varchar(255) default '' not null,
    city varchar(255) default '' not null,
    state varchar(20) default '' not null,
    zip varchar(15) default '' not null,
    suite varchar(100) default '' null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null,
    constraint injury_uuid
        unique (injury_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create index adj_number
    on cse_injury (adj_number);

create index deleted
    on cse_injury (deleted);

create index end_date
    on cse_injury (end_date);

create index occupation
    on cse_injury (occupation);

create index start_date
    on cse_injury (start_date);

create table cse_injury_accident
(
    injury_accident_id int auto_increment
        primary key,
    injury_accident_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    accident_uuid varchar(55) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(254) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index accident_uuid
    on cse_injury_accident (accident_uuid);

create index injury_uuid
    on cse_injury_accident (injury_uuid);

create table cse_injury_bodyparts
(
    injury_bodyparts_id int auto_increment
        primary key,
    injury_bodyparts_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    bodyparts_uuid varchar(15) not null,
    attribute varchar(20) not null,
    status enum('Y', 'N') default 'Y' null comment 'Y is for accepted, N is for rejected',
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index bodyparts_uuid
    on cse_injury_bodyparts (bodyparts_uuid);

create index injury_uuid
    on cse_injury_bodyparts (injury_uuid);

create table cse_injury_corporation
(
    injury_corporation_id int auto_increment
        primary key,
    injury_corporation_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    corporation_uuid varchar(15) not null,
    attribute varchar(100) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(15) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_injury_corporation (corporation_uuid);

create index injury_uuid
    on cse_injury_corporation (injury_uuid);

create table cse_injury_disability
(
    injury_disability_id int auto_increment
        primary key,
    injury_disability_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    disability_uuid varchar(55) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(254) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index disability_uuid
    on cse_injury_disability (disability_uuid);

create index injury_uuid
    on cse_injury_disability (injury_uuid);

create table cse_injury_document
(
    injury_document_id int auto_increment
        primary key,
    injury_document_uuid varchar(15) not null,
    injury_uuid varchar(15) default '' not null,
    document_uuid varchar(15) default '' not null,
    attribute_1 varchar(30) default '' not null,
    attribute_2 varchar(30) default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute_1
    on cse_injury_document (attribute_1);

create index document_uuid
    on cse_injury_document (document_uuid);

create index injury_uuid
    on cse_injury_document (injury_uuid);

create table cse_injury_event
(
    injury_event_id int auto_increment
        primary key,
    injury_event_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    event_uuid varchar(55) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index event_uuid
    on cse_injury_event (event_uuid);

create index injury_uuid
    on cse_injury_event (injury_uuid);

create table cse_injury_fee
(
    injury_fee_id int auto_increment
        primary key,
    injury_fee_uuid varchar(15) not null,
    fee_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(75) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_injury_fee (deleted);

create index fee_uuid
    on cse_injury_fee (fee_uuid);

create index injury_uuid
    on cse_injury_fee (injury_uuid);

create table cse_injury_injury_number
(
    injury_injury_number_id int auto_increment
        primary key,
    injury_injury_number_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    injury_number_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index injury_number_uuid
    on cse_injury_injury_number (injury_number_uuid);

create index injury_uuid
    on cse_injury_injury_number (injury_uuid);

create table cse_injury_lien
(
    injury_lien_id int auto_increment
        primary key,
    injury_lien_uuid varchar(15) not null,
    lien_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(15) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_injury_lien (deleted);

create index injury_uuid
    on cse_injury_lien (injury_uuid);

create index lien_uuid
    on cse_injury_lien (lien_uuid);

create table cse_injury_notes
(
    injury_notes_id int auto_increment
        primary key,
    injury_notes_uuid varchar(15) charset latin1 not null,
    injury_uuid varchar(15) charset latin1 not null,
    notes_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) charset latin1 not null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_injury_notes (deleted);

create index injury_uuid
    on cse_injury_notes (injury_uuid);

create index notes_uuid
    on cse_injury_notes (notes_uuid);

create index user_uuid
    on cse_injury_notes (last_update_user);

create table cse_injury_number
(
    injury_number_id int auto_increment
        primary key,
    injury_number_uuid varchar(15) not null,
    insurance_policy_number varchar(255) default '' not null,
    alternate_policy_number varchar(255) default '' not null,
    carrier_claim_number varchar(255) default '' not null,
    alternate_claim_number varchar(255) default '' not null,
    carrier_building_indentifier varchar(255) default '' not null,
    carrier_building_description varchar(255) default '' not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_injury_number_track
(
    injury_number_track_id int auto_increment
        primary key,
    user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    injury_number_id int not null,
    injury_number_uuid varchar(15) not null,
    insurance_policy_number varchar(255) default '' not null,
    alternate_policy_number varchar(255) default '' not null,
    carrier_claim_number varchar(255) default '' not null,
    alternate_claim_number varchar(255) default '' not null,
    carrier_building_indentifier varchar(255) default '' not null,
    carrier_building_description varchar(255) default '' not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_injury_occupation
(
    injury_occupation_id int not null,
    injury_occupation_uuid varchar(15) charset latin1 not null,
    injury_uuid varchar(15) charset latin1 not null,
    occupation_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) charset latin1 not null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_injury_other_dates
(
    cse_injury_other_dates int auto_increment
        primary key,
    adjunction_filing varchar(255) not null,
    adjunction_deadline varchar(255) not null,
    dor_filing varchar(255) not null,
    dor_deadline varchar(255) not null,
    serious_willful_filing varchar(255) not null,
    serious_willful_deadline varchar(255) not null,
    liability_filing varchar(255) not null,
    liability_deadline varchar(255) not null,
    disability_filing varchar(255) not null,
    disability_deadline varchar(255) not null,
    termination_date varchar(255) not null,
    termination_filing varchar(255) not null,
    termination_deadline varchar(255) not null,
    statute_of_limitations varchar(255) not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_injury_person
(
    injury_person_id int auto_increment
        primary key,
    injury_person_uuid varchar(15) not null,
    injury_uuid varchar(15) default '' not null,
    person_uuid varchar(15) default '' not null,
    attribute_1 varchar(30) default '' not null,
    attribute_2 varchar(30) default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute_1
    on cse_injury_person (attribute_1);

create index injury_uuid
    on cse_injury_person (injury_uuid);

create index person_uuid
    on cse_injury_person (person_uuid);

create table cse_injury_personx
(
    injury_personx_id int auto_increment
        primary key,
    injury_personx_uuid varchar(15) not null,
    injury_uuid varchar(15) default '' not null,
    personx_uuid varchar(15) default '' not null,
    attribute_1 varchar(30) default '' not null,
    attribute_2 varchar(30) default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute_1
    on cse_injury_personx (attribute_1);

create index injury_uuid
    on cse_injury_personx (injury_uuid);

create index personx_uuid
    on cse_injury_personx (personx_uuid);

create table cse_injury_settlement
(
    injury_settlement_id int auto_increment
        primary key,
    injury_settlement_uuid varchar(15) not null,
    settlement_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(75) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_injury_settlement (attribute);

create index deleted
    on cse_injury_settlement (deleted);

create index injury_uuid
    on cse_injury_settlement (injury_uuid);

create index settlement_uuid
    on cse_injury_settlement (settlement_uuid);

create table cse_injury_task
(
    injury_task_id int auto_increment
        primary key,
    injury_task_uuid varchar(15) charset latin1 not null,
    injury_uuid varchar(15) charset latin1 not null,
    task_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) charset latin1 not null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_injury_task (deleted);

create index injury_uuid
    on cse_injury_task (injury_uuid);

create index task_uuid
    on cse_injury_task (task_uuid);

create index user_uuid
    on cse_injury_task (last_update_user);

create table cse_injury_track
(
    injury_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    injury_id int not null,
    injury_uuid varchar(15) not null,
    adj_number varchar(255) not null,
    type varchar(50) not null,
    occupation varchar(255) not null,
    occupation_group varchar(50) default '' null,
    start_date date not null,
    end_date date not null,
    explanation text not null,
    deu enum('Y', 'N') default 'N' not null,
    full_address varchar(255) not null,
    suite varchar(100) null,
    customer_id int not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_injury_trigger
(
    injury_trigger_id int auto_increment
        primary key,
    injury_trigger_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    trigger_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(75) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_injury_trigger (deleted);

create table cse_injury_venue
(
    injury_venue_id int auto_increment
        primary key,
    injury_venue_uuid varchar(15) not null,
    injury_uuid varchar(15) not null,
    venue_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_injury_venue (deleted);

create index injury_uuid
    on cse_injury_venue (injury_uuid);

create index venue_uuid
    on cse_injury_venue (venue_uuid);

create table cse_invoice
(
    invoice_id int auto_increment
        primary key,
    invoice_uuid varchar(15) default '' not null,
    invoice_date timestamp default CURRENT_TIMESTAMP not null,
    notification_date datetime default '0000-00-00 00:00:00' null,
    reminder_date datetime default '0000-00-00 00:00:00' null,
    paid_date datetime default '0000-00-00 00:00:00' null,
    start_date date default '0000-00-00' null comment 'invoice date range start',
    end_date date default '0000-00-00' null,
    invoice_number varchar(45) default '' null,
    total decimal(7,2) default 0.00 null,
    payments decimal(7,2) default 0.00 null,
    invoice_items text null,
    active_users text null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null,
    id_collection varchar(1000) null,
    constraint invoice_uuid
        unique (invoice_uuid)
)
    comment 'invoice for activity' engine=MyISAM collate=utf8_unicode_ci;

create table cse_invoice_activity
(
    invoice_activity_id int auto_increment
        primary key,
    invoice_activity_uuid varchar(15) not null,
    invoice_uuid varchar(15) not null,
    activity_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(15) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index activity_uuid
    on cse_invoice_activity (activity_uuid);

create index deleted
    on cse_invoice_activity (deleted);

create index invouce_uuid
    on cse_invoice_activity (invoice_uuid);

create table cse_invoice_check
(
    invoice_check_id int auto_increment
        primary key,
    invoice_check_uuid varchar(15) not null,
    invoice_uuid varchar(15) not null,
    check_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index check_uuid
    on cse_invoice_check (check_uuid);

create index invoice_uuid
    on cse_invoice_check (invoice_uuid);

create table cse_invoice_reminder
(
    invoice_reminder_id int auto_increment
        primary key,
    invoice_reminder_uuid varchar(15) not null,
    invoice_uuid varchar(15) not null,
    reminder_uuid varchar(15) not null,
    attribute_1 varchar(255) default '' not null,
    attribute_2 varchar(255) default '' not null,
    last_updated_date varchar(255) default '' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index invoice_uuid
    on cse_invoice_reminder (invoice_uuid);

create index reminder_uuid
    on cse_invoice_reminder (reminder_uuid);

create table cse_ip_location
(
    ip_id int auto_increment
        primary key,
    ip_address varchar(255) default '' null,
    longitude decimal(9,5) default 0.00000 null,
    latitude decimal(9,5) default 0.00000 null,
    city varchar(255) default '' null,
    state varchar(45) default '' null,
    zip varchar(15) default '' null
);

create table cse_ipaddress
(
    ipaddress_id int auto_increment
        primary key,
    ipaddress_uuid varchar(15) null,
    ipaddresses varchar(300) null,
    constraint ip_address_uuid_UNIQUE
        unique (ipaddress_uuid)
);

create table cse_jetfile
(
    jetfile_id int auto_increment
        primary key,
    injury_uuid varchar(255) not null,
    info text null comment 'JSON format for all the forms info',
    jetfile_case_id int default 0 null,
    app_filing_id int default 0 null,
    app_filing_date datetime default '0000-00-00 00:00:00' null,
    app_status varchar(5055) default '' null,
    app_status_number int default 0 null,
    dor_info text null,
    jetfile_dor_id int default 0 null,
    dor_filing_id int default 0 null,
    dor_filing_date datetime default '0000-00-00 00:00:00' null,
    dore_info text null,
    jetfile_dore_id int default 0 null,
    dore_filing_id int default 0 null,
    dore_filing_date datetime default '0000-00-00 00:00:00' null,
    lien_info text null,
    jetfile_lien_id int default 0 null,
    lien_filing_id int default 0 null,
    lien_filing_date datetime default '0000-00-00 00:00:00' null,
    unstruc_info text null,
    customer_id int default 0 null,
    last_update_date datetime default '0000-00-00 00:00:00' null,
    deleted enum('Y', 'N') charset utf8 default 'N' null
)
    collate=utf8_unicode_ci;

create index injury_uuid
    on cse_jetfile (injury_uuid);

create table cse_job
(
    job_id int auto_increment
        primary key,
    job_uuid varchar(15) not null,
    job varchar(50) not null,
    blurb varchar(30) not null,
    color varchar(50) not null,
    constraint blurb
        unique (blurb),
    constraint job_uuid
        unique (job_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_kinvoice
(
    kinvoice_id int auto_increment
        primary key,
    kinvoice_uuid varchar(15) default '' not null,
    parent_kinvoice_uuid varchar(15) default '' null,
    kinvoice_date timestamp default CURRENT_TIMESTAMP not null,
    kinvoice_type enum('P', 'I') default 'I' null comment 'P = pre-bill
I = invoice',
    fund_transfer enum('P', 'C') default 'P' null comment 'P = pending
C = confirmed',
    notification_date datetime default '0000-00-00 00:00:00' null,
    reminder_date datetime default '0000-00-00 00:00:00' null,
    paid_date datetime default '0000-00-00 00:00:00' null,
    start_date date default '0000-00-00' null comment 'kinvoice date range start',
    end_date date default '0000-00-00' null,
    kinvoice_number varchar(45) default '' null,
    invoice_counter smallint(11) default 0 null comment 'keep track of number of invoices for the case',
    hourly_rate decimal(7,2) default 0.00 null,
    total decimal(7,2) default 0.00 null,
    payments decimal(7,2) default 0.00 null,
    comments varchar(1055) default '' null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null,
    template enum('Y', 'N') default 'N' null,
    template_name varchar(255) default '' null,
    constraint kinvoice_uuid
        unique (kinvoice_uuid)
)
    comment 'invoice for kase invoices, links to cse_kinvoiceitems' engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_kinvoice (customer_id);

create index deleted
    on cse_kinvoice (deleted);

create index template
    on cse_kinvoice (template);

create table cse_kinvoice_check
(
    kinvoice_check_id int auto_increment
        primary key,
    kinvoice_check_uuid varchar(15) charset latin1 not null,
    kinvoice_uuid varchar(15) charset latin1 not null,
    check_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index kinvoice_uuid
    on cse_kinvoice_check (kinvoice_uuid);

create index payment_uuid
    on cse_kinvoice_check (check_uuid);

create table cse_kinvoice_track
(
    kinvoice_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    kinvoice_id int not null,
    kinvoice_uuid varchar(15) default '' not null,
    parent_kinvoice_uuid varchar(15) default '' null,
    kinvoice_date datetime default '0000-00-00 00:00:00' not null,
    kinvoice_type enum('P', 'I') default 'I' null comment 'P = pre-bill
I = invoice',
    fund_transfer enum('P', 'C') default 'P' null comment 'P = pending
C = confirmed',
    notification_date datetime default '0000-00-00 00:00:00' null,
    reminder_date datetime default '0000-00-00 00:00:00' null,
    paid_date datetime default '0000-00-00 00:00:00' null,
    start_date date default '0000-00-00' null comment 'kinvoice date range start',
    end_date date default '0000-00-00' null,
    kinvoice_number varchar(45) default '' null,
    invoice_counter smallint(11) default 0 null comment 'keep track of number of invoices for the case',
    hourly_rate decimal(7,2) default 0.00 null,
    total decimal(7,2) default 0.00 null,
    payments decimal(7,2) default 0.00 null,
    comments varchar(1055) default '' null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null,
    template enum('Y', 'N') default 'N' null,
    template_name varchar(255) default '' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index kinvoice_id
    on cse_kinvoice_track (kinvoice_id);

create index operation
    on cse_kinvoice_track (operation);

create table cse_kinvoiceitem
(
    kinvoiceitem_id int auto_increment
        primary key,
    kinvoiceitem_uuid varchar(15) null,
    kinvoice_uuid varchar(15) default '' null,
    activity_uuid varchar(15) default '' null,
    item_name varchar(1055) default '' null,
    item_description varchar(1055) default '' null,
    exact enum('Y', 'N') default 'N' null comment 'no hours required, exact amount only',
    minutes int default 0 null,
    rate decimal(7,2) default 0.00 null,
    amount decimal(7,2) default 0.00 null,
    unit varchar(45) default '' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    collate=utf8_unicode_ci;

create index activity_uuid
    on cse_kinvoiceitem (activity_uuid);

create index deleted
    on cse_kinvoiceitem (deleted);

create index item_uuid
    on cse_kinvoiceitem (kinvoiceitem_uuid);

create index kinvoice_uuid
    on cse_kinvoiceitem (kinvoice_uuid);

create table cse_letter
(
    letter_id int auto_increment
        primary key,
    letter_uuid varchar(15) charset latin1 default '' not null,
    type varchar(100) charset latin1 default 'general' not null,
    subject varchar(255) not null,
    letter text charset latin1 not null,
    title varchar(255) charset latin1 not null,
    attachments varchar(255) not null,
    entered_by varchar(255) charset latin1 default 'SYSTEM' not null,
    status varchar(50) charset latin1 default 'STANDARD' not null,
    dateandtime timestamp default CURRENT_TIMESTAMP not null,
    days int not null,
    verified enum('Y', 'N') charset latin1 default 'N' not null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index letters_uuid
    on cse_letter (letter_uuid);

create table cse_letter_document
(
    letters_document_id int auto_increment
        primary key,
    letters_document_uuid varchar(15) not null,
    letter_uuid varchar(15) not null,
    document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index document_uuid
    on cse_letter_document (document_uuid);

create index letters_uuid
    on cse_letter_document (letter_uuid);

create table cse_lien
(
    lien_id int auto_increment
        primary key,
    lien_uuid varchar(15) not null,
    date_filed date not null,
    date_paid date default '0000-00-00' null,
    amount_of_lien decimal(9,2) default 0.00 not null,
    amount_of_fee decimal(9,2) default 0.00 not null,
    amount_paid decimal(9,2) default 0.00 null,
    appearance_fee decimal(9,2) default 0.00 null,
    worker varchar(255) default '' not null,
    customer_id int not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_lien_track
(
    lien_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    lien_id int not null,
    lien_uuid varchar(15) not null,
    date_filed date not null,
    date_paid date default '0000-00-00' null,
    amount_of_lien decimal(9,2) default 0.00 not null,
    amount_of_fee decimal(9,2) default 0.00 not null,
    amount_paid decimal(9,2) default 0.00 null,
    appearance_fee decimal(9,2) default 0.00 null,
    worker varchar(255) not null,
    customer_id int not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_lostincome
(
    lostincome_id int auto_increment
        primary key,
    lostincome_uuid varchar(15) default '' null,
    start_lost_date date default '0000-00-00' null,
    end_lost_date date default '0000-00-00' null,
    comments varchar(1055) charset utf8 default '' null,
    amount decimal(11,2) default 0.00 null,
    wage decimal(11,2) default 0.00 null,
    per enum('H', 'D', 'W', 'M', 'Y', '') default 'H' null comment '''H'' = HOURLY
''D'' = DAILY
''W'' = WEEKLY
''M'' = MONLTHLY
''Y'' = YEARLY',
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    collate=utf8_unicode_ci;

create index customer_id
    on cse_lostincome (customer_id);

create index deleted
    on cse_lostincome (deleted);

create index lostincome_uuid
    on cse_lostincome (lostincome_uuid);

create table cse_medical_specialties
(
    specialty_id int auto_increment
        primary key,
    specialty varchar(36) null,
    description varchar(222) null
)
    engine=MyISAM;

create table cse_medicalbilling
(
    medicalbilling_id int auto_increment
        primary key,
    medicalbilling_uuid varchar(15) default '' null,
    corporation_uuid varchar(15) default '' null,
    user_uuid varchar(145) default '' null comment 'user_uuid of authorizing user',
    bill_date date default '0000-00-00' null,
    billed decimal(11,2) default 0.00 null,
    paid decimal(11,2) default 0.00 null,
    adjusted decimal(11,2) default 0.00 null,
    balance decimal(11,2) default 0.00 null,
    override decimal(11,2) default 0.00 null,
    finalized date default '0000-00-00' null,
    still_treating enum('Y', 'N') default 'N' null,
    prior enum('Y', 'N') default 'N' null,
    lien enum('Y', 'N') default 'N' null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    comment 'keep track of medical billing items for settlements' collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_medicalbilling (corporation_uuid);

create index medicalbilling_uuid
    on cse_medicalbilling (medicalbilling_uuid);

create table cse_medicalbilling_track
(
    medicalbilling_track_id int auto_increment
        primary key,
    track_user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    medicalbilling_id int not null,
    medicalbilling_uuid varchar(15) default '' null,
    corporation_uuid varchar(15) default '' null,
    user_uuid varchar(145) default '' null comment 'user_uuid of authorizing user',
    bill_date date default '0000-00-00' null,
    billed decimal(11,2) default 0.00 null,
    paid decimal(11,2) default 0.00 null,
    adjusted decimal(11,2) default 0.00 null,
    balance decimal(11,2) default 0.00 null,
    override decimal(11,2) default 0.00 null,
    finalized date default '0000-00-00' null,
    still_treating enum('Y', 'N') default 'N' null,
    prior enum('Y', 'N') default 'N' null,
    lien enum('Y', 'N') default 'N' null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_medicalspecialties
(
    specialty varchar(255) default '' not null
        primary key,
    description varchar(255) default '' null
);

create table cse_medication
(
    medication_id int auto_increment
        primary key,
    case_uuid varchar(15) default '' null,
    medication_info text charset utf8 null comment 'data as json',
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    comment 'medication info' collate=utf8_unicode_ci;

create table cse_message
(
    message_id int auto_increment
        primary key,
    message_uuid varchar(155) not null,
    message_type varchar(100) default 'normal' not null,
    dateandtime datetime default '0000-00-00 00:00:00' not null,
    `from` varchar(255) default '' not null,
    message_to varchar(255) default '' not null,
    message_cc varchar(255) default '' not null,
    message_bcc varchar(255) default '' not null,
    message longtext not null,
    subject varchar(255) default '' not null,
    snippet varchar(1055) default '' null,
    attachments varchar(1055) default '' not null,
    priority varchar(255) default '' not null,
    callback_date datetime default '0000-00-00 00:00:00' not null,
    customer_id int default 0 not null,
    status enum('created', 'sent', 'buffered', 'scheduled', 'approved', 'rejected', '') default '' not null,
    deleted enum('Y', 'N', 'D') default 'N' not null comment 'D is for Draft'
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_message (customer_id);

create index dateandtime
    on cse_message (dateandtime);

create index deleted
    on cse_message (deleted);

create index message_type
    on cse_message (message_type);

create index message_uuid
    on cse_message (message_uuid);

create table cse_message_attachments
(
    message_id int default 0 not null,
    message_uuid varchar(155) not null,
    message_attachments text charset latin1 null,
    created varchar(19) default '' not null,
    constraint message_id_UNIQUE
        unique (message_id)
)
    collate=utf8_unicode_ci;

create index message_uuid
    on cse_message_attachments (message_uuid);

create table cse_message_contact
(
    message_contact_id int auto_increment
        primary key,
    message_contact_uuid varchar(15) not null,
    message_uuid varchar(155) default '' not null,
    message_id int default 0 null,
    contact_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_message_contact (attribute);

create index contact_uuid
    on cse_message_contact (contact_uuid);

create index customer_id
    on cse_message_contact (customer_id);

create index deleted
    on cse_message_contact (deleted);

create index message_uuid
    on cse_message_contact (message_uuid);

create table cse_message_document
(
    message_document_id int auto_increment
        primary key,
    message_document_uuid varchar(15) not null,
    message_uuid varchar(15) not null,
    document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) default '' not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index document_uuid
    on cse_message_document (document_uuid);

create index message_uuid
    on cse_message_document (message_uuid);

create table cse_message_from
(
    message_id int default 0 null,
    user_id int default 0 null,
    user_name varchar(255) charset latin1 default '' null
)
    collate=utf8_unicode_ci;

create index message_id
    on cse_message_from (message_id);

create index user_id
    on cse_message_from (user_id);

create table cse_message_kinvoice
(
    message_kinvoice_id int auto_increment
        primary key,
    message_kinvoice_uuid varchar(15) not null,
    message_uuid varchar(15) not null,
    kinvoice_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) default '' not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index kinvoice_uuid
    on cse_message_kinvoice (kinvoice_uuid);

create index message_uuid
    on cse_message_kinvoice (message_uuid);

create table cse_message_reaction
(
    reaction_id int auto_increment
        primary key,
    message_uuid varchar(155) default '' null,
    user_uuid varchar(45) default '' null,
    reply_date datetime default '0000-00-00 00:00:00' null,
    forward_date datetime default '0000-00-00 00:00:00' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    collate=utf8_unicode_ci;

create index message_uuid
    on cse_message_reaction (message_uuid);

create index user_uuid
    on cse_message_reaction (user_uuid);

create table cse_message_track
(
    message_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null,
    message_id int not null,
    message_uuid varchar(155) not null,
    message_type varchar(100) default 'normal' not null,
    dateandtime datetime default '0000-00-00 00:00:00' not null,
    `from` varchar(255) default '' not null,
    message_to varchar(255) default '' not null,
    message_cc varchar(255) default '' not null,
    message_bcc varchar(255) default '' not null,
    message longtext not null,
    subject varchar(255) default '' not null,
    snippet varchar(1055) default '' null,
    attachments varchar(255) default '' not null,
    priority varchar(255) default '' not null,
    callback_date datetime default '0000-00-00 00:00:00' not null,
    customer_id int default 0 not null,
    status enum('created', 'sent', 'buffered', 'scheduled', '') default '' not null,
    deleted enum('Y', 'N', 'D') default 'N' not null comment 'D is for Draft'
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_message_user
(
    message_user_id int auto_increment
        primary key,
    message_user_uuid varchar(15) not null,
    message_uuid varchar(155) not null,
    user_uuid varchar(45) not null,
    message_id int default 0 null,
    user_id int default 0 null,
    type enum('from', 'to', 'cc', 'bcc') default 'to' not null,
    thread_uuid varchar(155) not null,
    read_status enum('Y', 'N') default 'N' not null,
    read_date datetime default '0000-00-00 00:00:00' not null,
    action enum('reply', 'forward', 'reminder') not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    user_type enum('user', 'person', 'corporation') default 'user' null comment 'some user_uuids are person_uuid or corporation_uuid'
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_message_user (deleted);

create index message_id
    on cse_message_user (message_id);

create index message_uuid
    on cse_message_user (message_uuid);

create index read_date
    on cse_message_user (read_date);

create index read_status
    on cse_message_user (read_status);

create index type
    on cse_message_user (type);

create index user_id
    on cse_message_user (user_id);

create index user_uuid
    on cse_message_user (user_uuid);

create table cse_negotiation
(
    negotiation_id int auto_increment
        primary key,
    negotiation_uuid varchar(15) default '' null,
    negotiation_date datetime default '0000-00-00 00:00:00' null,
    negotiator varchar(255) default '' null,
    firm varchar(255) default '' null comment 'will update with corp_uuid eventually',
    worker varchar(5) default '' null,
    negotiation_type enum('O', 'D') default 'O' null comment 'O = Offer
D = Demand',
    amount decimal(11,2) default 0.00 null,
    comments varchar(1055) default '' null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    collate=utf8_unicode_ci;

create index deleted
    on cse_negotiation (deleted);

create index negotiation_uuid
    on cse_negotiation (negotiation_uuid);

create index worker
    on cse_negotiation (worker);

create table cse_negotiation_track
(
    negotiation_track_id int auto_increment
        primary key,
    user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    negotiation_id int not null,
    negotiation_uuid varchar(15) default '' null,
    negotiation_date datetime default '0000-00-00 00:00:00' null,
    negotiator varchar(255) default '' null,
    firm varchar(255) default '' null comment 'will update with corp_uuid eventually',
    worker varchar(5) default '' null,
    negotiation_type enum('O', 'D') default 'O' null comment 'O = Offer
D = Demand',
    amount decimal(11,2) default 0.00 null,
    comments varchar(1055) default '' null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_negotiation_track (customer_id);

create index negotiation_id
    on cse_negotiation_track (negotiation_id);

create index operation
    on cse_negotiation_track (operation);

create index user_uuid
    on cse_negotiation_track (user_uuid);

create table cse_new_legal
(
    new_legal_id int auto_increment
        primary key,
    new_legal_uuid varchar(15) collate utf8_unicode_ci default '' null,
    new_legal_date datetime default '0000-00-00 00:00:00' null,
    new_legal_description text null,
    new_legal_info text null,
    new_legal_details text null,
    new_legal_other_details text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null,
    case_id int null
);

create table cse_notes
(
    notes_id int auto_increment
        primary key,
    notes_uuid varchar(255) charset latin1 default '' not null,
    type varchar(100) charset latin1 default 'general' not null,
    subject varchar(255) default '' not null,
    note longtext charset latin1 not null,
    title varchar(255) charset latin1 default '' not null,
    attachments varchar(1055) default '' not null,
    entered_by varchar(255) charset latin1 default 'SYSTEM' not null,
    status varchar(50) charset latin1 default 'STANDARD' not null,
    dateandtime timestamp default CURRENT_TIMESTAMP not null,
    callback_date datetime default '0000-00-00 00:00:00' not null,
    verified enum('Y', 'N') charset latin1 default 'N' not null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_notes (customer_id);

create index note_uuid
    on cse_notes (notes_uuid);

create index type
    on cse_notes (type);

create table cse_notes_customer
(
    notes_customer_id int auto_increment
        primary key,
    notes_customer_uuid varchar(15) not null,
    notes_uuid varchar(255) charset latin1 default '' not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    notes_id int null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index notes_uuid
    on cse_notes_customer (notes_uuid);

create table cse_notes_document
(
    notes_document_id int auto_increment
        primary key,
    notes_document_uuid varchar(15) not null,
    notes_uuid varchar(255) charset latin1 default '' not null,
    document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) default '' not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index document_uuid
    on cse_notes_document (document_uuid);

create index notes_uuid
    on cse_notes_document (notes_uuid);

create table cse_notes_task
(
    notes_task_id int auto_increment
        primary key,
    notes_task_uuid varchar(15) charset latin1 not null,
    notes_uuid varchar(15) charset latin1 not null,
    task_uuid varchar(255) charset latin1 not null,
    attribute varchar(255) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute
    on cse_notes_task (attribute);

create index notes_uuid
    on cse_notes_task (notes_uuid);

create index task_uuid
    on cse_notes_task (task_uuid);

create table cse_notes_track
(
    notes_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    notes_id int not null,
    notes_uuid varchar(255) charset latin1 default '' not null,
    type varchar(100) default 'general' not null,
    note longtext charset latin1 not null,
    title varchar(255) charset latin1 default '' not null,
    subject varchar(255) default '' not null,
    attachments varchar(1055) default '' not null,
    entered_by varchar(255) default 'SYSTEM' not null,
    status varchar(50) default 'STANDARD' not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null,
    callback_date datetime default '0000-00-00 00:00:00' not null,
    verified enum('Y', 'N') default 'N' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_notification
(
    notification_id int auto_increment
        primary key,
    document_uuid varchar(45) null,
    notification_uuid varchar(45) null,
    user_uuid varchar(45) null,
    notifier varchar(45) charset utf8 default '' null,
    notification varchar(45) charset utf8 default 'review' null,
    notification_date datetime null,
    instructions varchar(1055) default '' null,
    read_date datetime default '0000-00-00 00:00:00' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') charset utf8 default 'N' null
)
    collate=utf8_unicode_ci;

create index document_uuid
    on cse_notification (document_uuid);

create index notification
    on cse_notification (notification);

create index user_uuid
    on cse_notification (user_uuid);

create table cse_notification_track
(
    notification_track_id int auto_increment
        primary key,
    notification_user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    notification_id int not null,
    document_uuid varchar(45) null,
    notification_uuid varchar(45) null,
    user_uuid varchar(45) null,
    notification varchar(45) default 'review' null,
    notification_date datetime null,
    instructions varchar(1055) default '' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_occupation
(
    occupation_id int auto_increment
        primary key,
    occupation_uuid varchar(10) default '' null,
    onetsoc_code char(10) not null,
    title varchar(150) not null,
    description varchar(1000) not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_owner
(
    owner_id int auto_increment
        primary key,
    admin_client varchar(25) charset latin1 default '' not null,
    name varchar(255) charset latin1 default '' not null,
    nickname varchar(4) default '' not null,
    owner_email varchar(255) charset latin1 null,
    url varchar(255) charset latin1 default '' not null,
    password varchar(255) charset latin1 default '' not null,
    pwd varchar(255) charset latin1 null comment 'hash password',
    role enum('owner', 'adminstrator') charset latin1 default 'owner' not null,
    session_id varchar(50) charset latin1 default '' not null,
    dateandtime datetime default '0000-00-00 00:00:00' not null,
    ip_address varchar(50) charset latin1 default '0.0.0.0' not null
)
    collate=utf8_unicode_ci;

create table cse_partie_type
(
    partie_type_id int auto_increment
        primary key,
    partie_type varchar(50) not null,
    employee_title varchar(100) default '' not null,
    blurb varchar(30) not null,
    color varchar(50) default '' not null,
    show_employee enum('Y', 'N') default 'N' not null,
    adhoc_fields varchar(255) default '' not null,
    sort_order int default 30 not null,
    constraint blurb
        unique (blurb)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_person
(
    person_id int auto_increment
        primary key,
    person_uuid varchar(15) not null,
    parent_person_uuid varchar(15) not null,
    full_name varchar(50) default '' not null,
    company_name varchar(255) default '' not null,
    first_name varchar(100) default '' not null,
    middle_name varchar(100) default '' not null,
    last_name varchar(100) default '' not null,
    aka varchar(50) default '' not null,
    preferred_name varchar(100) default '' not null,
    full_address varchar(255) default '' not null,
    longitude decimal(9,2) default 0.00 not null,
    latitude decimal(9,2) default 0.00 not null,
    street varchar(255) default '' not null,
    city varchar(100) default '' not null,
    state char(2) default '' not null,
    zip varchar(10) default '' not null,
    suite varchar(100) default '' not null,
    phone varchar(255) default '' not null,
    email varchar(255) default '' not null,
    fax varchar(255) default '' not null,
    work_phone varchar(255) default '' not null,
    cell_phone varchar(255) default '' not null,
    other_phone varchar(255) default '' not null,
    work_email varchar(255) default '' not null,
    ssn varchar(255) default '' not null,
    ssn_last_four char(4) default '' not null comment 'plain text last 4 digits of ssn for search purposes',
    ein varchar(10) default '' null comment 'Employer ID Number',
    dob varchar(15) default '' not null,
    license_number varchar(20) default '' not null,
    title varchar(100) default '' not null,
    ref_source varchar(50) default '' not null,
    salutation varchar(100) default '' not null,
    age int default 0 not null,
    priority_flag enum('Y', 'N', '') default '' not null,
    gender enum('F', 'M', '') default '' not null,
    language varchar(100) default '' not null,
    birth_state char(100) default '' not null,
    birth_city varchar(100) default '' not null,
    marital_status enum('Divorced', 'Married', 'Single', 'Seperated', 'Widowed', '') default '' not null comment 'Divorce, Married, Single',
    legal_status varchar(255) default '' not null,
    spouse varchar(255) default '' not null,
    spouse_contact varchar(255) default '' not null,
    emergency varchar(255) default '' not null,
    emergency_contact varchar(255) default '' not null,
    last_updated_date varchar(255) default '' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null,
    constraint person_uuid
        unique (person_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create index aka
    on cse_person (aka);

create index customer_id
    on cse_person (customer_id);

create index deleted
    on cse_person (deleted);

create index dob
    on cse_person (dob);

create index email
    on cse_person (email);

create index first_name
    on cse_person (first_name);

create index full_address
    on cse_person (full_address);

create index last_four
    on cse_person (ssn_last_four);

create index last_name
    on cse_person (last_name);

create index parent_uuid
    on cse_person (parent_person_uuid);

create index phone
    on cse_person (phone);

create index work_email
    on cse_person (work_email);

create index work_phone
    on cse_person (work_phone);

create table cse_person_check
(
    person_check_id int auto_increment
        primary key,
    person_check_uuid varchar(15) charset latin1 not null,
    person_uuid varchar(15) charset latin1 not null,
    check_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index check_uuid
    on cse_person_check (check_uuid);

create index person_uuid
    on cse_person_check (person_uuid);

create table cse_person_checkrequest
(
    person_checkrequest_id int auto_increment
        primary key,
    person_checkrequest_uuid varchar(15) charset latin1 not null,
    person_uuid varchar(15) charset latin1 not null,
    checkrequest_uuid varchar(25) default '' null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index checkrequest_uuid
    on cse_person_checkrequest (checkrequest_uuid);

create index person_uuid
    on cse_person_checkrequest (person_uuid);

create table cse_person_corporation
(
    person_corporation_id int auto_increment
        primary key,
    person_corporation_uuid varchar(15) not null,
    person_uuid varchar(15) not null,
    corporation_uuid varchar(15) not null,
    attribute_1 varchar(20) not null,
    attribute_2 varchar(20) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index corporation_uuid
    on cse_person_corporation (corporation_uuid);

create index person_uuid
    on cse_person_corporation (person_uuid);

create table cse_person_document
(
    person_document_id int auto_increment
        primary key,
    person_document_uuid varchar(15) not null,
    person_uuid varchar(15) not null,
    document_uuid varchar(15) not null,
    parent_document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    customer_id int not null,
    `delete` enum('0', '1') default '0' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index document_uuid
    on cse_person_document (document_uuid);

create index person_uuid
    on cse_person_document (person_uuid);

create table cse_person_notes
(
    person_notes_id int auto_increment
        primary key,
    person_notes_uuid varchar(15) charset latin1 not null,
    person_uuid varchar(15) charset latin1 not null,
    notes_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index notes_uuid
    on cse_person_notes (notes_uuid);

create index person_uuid
    on cse_person_notes (person_uuid);

create table cse_person_person
(
    person_person_id int auto_increment
        primary key,
    person_person_uuid varchar(15) not null,
    parent_uuid varchar(15) not null,
    child_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index child_uuid
    on cse_person_person (child_uuid);

create index parent_uuid
    on cse_person_person (parent_uuid);

create table cse_person_rx
(
    person_rx_id int auto_increment
        primary key,
    person_rx_uuid varchar(15) charset latin1 not null,
    person_uuid varchar(15) charset latin1 not null,
    rx_uuid varchar(15) charset latin1 not null,
    attribute varchar(50) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index person_uuid
    on cse_person_rx (person_uuid);

create index rx_uuid
    on cse_person_rx (rx_uuid);

create table cse_person_track
(
    person_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    person_id int null,
    person_uuid varchar(15) not null,
    parent_person_uuid varchar(15) not null,
    full_name varchar(50) not null,
    company_name varchar(255) not null,
    first_name varchar(100) not null,
    middle_name varchar(100) default '' null,
    last_name varchar(100) not null,
    aka varchar(50) not null,
    preferred_name varchar(100) not null,
    full_address varchar(255) not null,
    longitude decimal(9,2) default 0.00 not null,
    latitude decimal(9,2) default 0.00 not null,
    street varchar(255) not null,
    city varchar(100) not null,
    state char(2) not null,
    zip varchar(10) not null,
    suite varchar(100) not null,
    phone varchar(255) default '' not null,
    email varchar(255) not null,
    fax varchar(255) default '' not null,
    work_phone varchar(255) default '' not null,
    cell_phone varchar(255) default '' not null,
    other_phone varchar(255) default '' not null,
    work_email varchar(255) default '' not null,
    ssn varchar(255) not null,
    ssn_last_four char(4) not null comment 'plain text last 4 digits of ssn for search purposes',
    ein varchar(10) default '' null comment 'Employer ID Number',
    dob varchar(15) not null,
    license_number varchar(20) not null,
    title varchar(100) not null,
    ref_source varchar(50) not null,
    salutation varchar(100) not null,
    age int not null,
    priority_flag enum('Y', 'N', '') default 'N' not null,
    gender enum('F', 'M', '') default '' not null,
    language varchar(100) not null,
    birth_state char(2) not null,
    birth_city varchar(100) not null,
    marital_status varchar(255) default '' not null comment 'Divorce, Married, Single',
    legal_status varchar(255) default '' not null,
    spouse varchar(255) not null,
    spouse_contact varchar(255) not null,
    emergency varchar(255) not null,
    emergency_contact varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_personal_injury
(
    personal_injury_id int auto_increment
        primary key,
    personal_injury_uuid varchar(15) collate utf8_unicode_ci default '' null,
    case_id int null,
    personal_injury_date datetime default '0000-00-00 00:00:00' null,
    statute_limitation date default '0000-00-00' null,
    statute_interval int default 0 null,
    loss_date date default '0000-00-00' null,
    personal_injury_description text null,
    personal_injury_info text null,
    personal_injury_details text null,
    personal_injury_other_details text null,
    rental_info text null,
    repair_info text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
);

create index case_id
    on cse_personal_injury (case_id);

create index customer_id
    on cse_personal_injury (customer_id);

create index deleted
    on cse_personal_injury (deleted);

create index personal_injury_date
    on cse_personal_injury (personal_injury_date);

create index statute_limitation
    on cse_personal_injury (statute_limitation);

create table cse_personal_injury_old
(
    personal_injury_id int auto_increment
        primary key,
    personal_injury_uuid varchar(15) null,
    personal_injury_date datetime null,
    personal_injury_day varchar(45) null,
    personal_injury_time varchar(45) null,
    personal_injury_location varchar(400) null,
    personal_injury_county varchar(45) null,
    personal_injury_accident_description varchar(1000) null,
    personal_injury_other_details varchar(1000) null,
    customer_id int null,
    deleted enum('Y', 'N') default 'N' null,
    case_id varchar(45) null
);

create table cse_personal_injury_track
(
    personal_injury_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    personal_injury_id int default 0 not null,
    personal_injury_uuid varchar(15) default '' null,
    case_id int null,
    personal_injury_date datetime default '0000-00-00 00:00:00' null,
    statute_limitation date default '0000-00-00' null,
    statute_interval int default 0 null,
    loss_date date default '0000-00-00' null,
    personal_injury_description text null,
    personal_injury_info text null,
    personal_injury_details text null,
    personal_injury_other_details text null,
    rental_info text null,
    repair_info text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index personal_injury_id
    on cse_personal_injury_track (personal_injury_id);

create table cse_personal_injury_trigger
(
    personal_injury_trigger_id int auto_increment
        primary key,
    personal_injury_trigger_uuid varchar(15) not null,
    personal_injury_uuid varchar(15) not null,
    trigger_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(75) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_personal_injury_trigger (deleted);

create table cse_personx
(
    personx_id int auto_increment
        primary key,
    personx_uuid varchar(15) not null,
    parent_personx_uuid varchar(15) not null,
    full_name varbinary(128) not null,
    company_name varbinary(128) not null,
    first_name varbinary(128) not null,
    middle_name varbinary(128) not null,
    last_name varbinary(128) not null,
    aka varbinary(128) not null,
    preferred_name varbinary(128) not null,
    full_address varbinary(128) not null,
    longitude decimal(9,2) default 0.00 not null,
    latitude decimal(9,2) default 0.00 not null,
    street varbinary(128) not null,
    city varchar(100) default '' not null,
    state char(2) default '' not null,
    zip varchar(10) default '' not null,
    suite varbinary(128) not null,
    phone varbinary(128) not null,
    email varbinary(128) not null,
    fax varbinary(128) not null,
    work_phone varbinary(128) not null,
    cell_phone varbinary(128) not null,
    other_phone varbinary(128) not null,
    work_email varbinary(128) not null,
    ssn varbinary(128) not null,
    ssn_last_four varbinary(128) not null comment 'plain text last 4 digits of ssn for search purposes',
    ein varbinary(128) null,
    dob varbinary(128) not null,
    license_number varbinary(128) not null,
    title varchar(100) default '' not null,
    ref_source varbinary(128) not null,
    salutation varbinary(128) not null,
    age int default 0 not null,
    priority_flag enum('Y', 'N', '') default '' not null,
    gender enum('F', 'M', '') default '' not null,
    language varchar(100) default '' not null,
    birth_state varbinary(128) not null,
    birth_city varbinary(128) not null,
    marital_status enum('Divorced', 'Married', 'Single', 'Seperated', 'Widowed', '') default '' not null comment 'Divorce, Married, Single',
    legal_status varchar(255) default '' not null,
    spouse varbinary(128) not null,
    spouse_contact varbinary(128) not null,
    emergency varbinary(128) not null,
    emergency_contact varbinary(128) not null,
    last_updated_date varchar(255) default '' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null,
    constraint person_uuid
        unique (personx_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_personx (customer_id);

create index deleted
    on cse_personx (deleted);

create index parent_uuid
    on cse_personx (parent_personx_uuid);

create table cse_personx_track
(
    personx_track_id int auto_increment
        primary key,
    user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    personx_id int null,
    personx_uuid varchar(15) not null,
    parent_personx_uuid varchar(15) not null,
    full_name varbinary(128) not null,
    company_name varbinary(128) not null,
    first_name varbinary(128) not null,
    middle_name varbinary(128) not null,
    last_name varbinary(128) not null,
    aka varbinary(128) not null,
    preferred_name varbinary(128) not null,
    full_address varbinary(128) not null,
    longitude decimal(9,2) default 0.00 not null,
    latitude decimal(9,2) default 0.00 not null,
    street varbinary(128) not null,
    city varchar(100) default '' not null,
    state char(2) default '' not null,
    zip varchar(10) default '' not null,
    suite varbinary(128) not null,
    phone varbinary(128) not null,
    email varbinary(128) not null,
    fax varbinary(128) not null,
    work_phone varbinary(128) not null,
    cell_phone varbinary(128) not null,
    other_phone varbinary(128) not null,
    work_email varbinary(128) not null,
    ssn varbinary(128) not null,
    ssn_last_four varbinary(128) not null comment 'plain text last 4 digits of ssn for search purposes',
    ein varbinary(128) null,
    dob varbinary(128) not null,
    license_number varbinary(128) not null,
    title varchar(100) default '' not null,
    ref_source varbinary(128) not null,
    salutation varbinary(128) not null,
    age int default 0 not null,
    priority_flag enum('Y', 'N', '') default '' not null,
    gender enum('F', 'M', '') default '' not null,
    language varchar(100) default '' not null,
    birth_state varbinary(128) not null,
    birth_city varbinary(128) not null,
    marital_status varchar(255) default '' not null comment 'Divorce, Married, Single',
    legal_status varchar(255) default '' not null,
    spouse varbinary(128) not null,
    spouse_contact varbinary(128) not null,
    emergency varbinary(128) not null,
    emergency_contact varbinary(128) not null,
    last_updated_date varchar(255) default '' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_pi_document
(
    pi_document_id int auto_increment
        primary key,
    pi_document_uuid varchar(15) not null,
    case_uuid varchar(15) default '' not null,
    document_uuid varchar(15) default '' not null,
    attribute_1 varchar(30) default '' not null,
    attribute_2 varchar(30) default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int default 0 not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index attribute_2
    on cse_pi_document (attribute_2);

create index case_uuid
    on cse_pi_document (case_uuid);

create index document_uuid
    on cse_pi_document (document_uuid);

create table cse_private
(
    private_id int auto_increment
        primary key,
    private_uuid varchar(15) not null,
    customer_id int default 1033 not null,
    category varchar(100) default '' not null,
    private varchar(50) default '' not null,
    private_value varbinary(128) not null,
    private_type varchar(255) default '' not null comment 'color, date, choice, etc...',
    default_value varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_uuid
    on cse_private (customer_id);

create index private_uuid
    on cse_private (private_uuid);

create table cse_project
(
    project_id int auto_increment
        primary key,
    project_uuid varchar(15) not null,
    dateandtime datetime not null,
    `from` varchar(255) not null,
    subject varchar(255) not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_project_task
(
    project_task_id int auto_increment
        primary key,
    project_task_uuid varchar(15) not null,
    project_uuid varchar(15) not null comment 'this can be a uuid or an email address',
    task_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(15) not null comment 'task uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index project_uuid
    on cse_project_task (project_uuid);

create index task_uuid
    on cse_project_task (task_uuid);

create table cse_rate
(
    rate_id int auto_increment
        primary key,
    rate_uuid varchar(15) default '' null,
    case_type varchar(255) default '' null,
    create_date timestamp default CURRENT_TIMESTAMP null,
    rate_description varchar(1055) default '' null,
    rate_name varchar(45) charset utf8 default '' null,
    rate_info text charset utf8 null comment 'json of rate values',
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    collate=utf8_unicode_ci;

create table cse_regs
(
    trial_calendar_id int auto_increment
        primary key,
    days int(20) not null,
    prior_type varchar(255) not null,
    alt_days int(20) not null,
    alt_prior_type varchar(255) not null,
    due text not null
);

create table cse_reminder
(
    reminder_id int auto_increment
        primary key,
    reminder_uuid varchar(15) default '' not null,
    reminder_number tinyint default 1 not null,
    reminder_type varchar(15) default '' not null,
    reminder_interval int default 0 not null,
    reminder_span enum('minutes', 'hours', 'days', 'weeks', '') default '' not null,
    reminder_datetime datetime default '0000-00-00 00:00:00' not null comment 'actual date and time for reminder',
    verified enum('Y', 'N') default 'Y' not null,
    buffered enum('Y', 'N') default 'N' not null,
    sent enum('Y', 'N') default 'N' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' not null,
    constraint uuid
        unique (reminder_uuid)
)
    engine=MyISAM charset=latin1;

create index reminder_datetime
    on cse_reminder (reminder_datetime);

create table cse_reminder_message
(
    reminder_message_id int auto_increment
        primary key,
    reminder_message_uuid varchar(15) not null,
    reminder_uuid varchar(15) not null,
    message_uuid varchar(155) not null,
    attribute varchar(255) charset latin1 default '' not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_reminder_message (deleted);

create index message_uuid
    on cse_reminder_message (message_uuid);

create index reminder_uuid
    on cse_reminder_message (reminder_uuid);

create table cse_reminderbuffer
(
    reminderbuffer_id int auto_increment
        primary key,
    message_uuid varchar(55) not null,
    reminder_uuid varchar(55) null,
    `from` varchar(255) not null,
    from_address varchar(255) default '' not null,
    recipients text not null,
    `to` varchar(255) default '' not null,
    cc varchar(255) default '' not null,
    bcc varchar(255) default '' not null,
    subject varchar(255) not null,
    message text not null,
    attachments varchar(255) default '' not null,
    timestamp timestamp default CURRENT_TIMESTAMP not null,
    buffer_error varchar(1055) default '' null,
    customer_id int not null,
    deleted enum('Y', 'N', 'E') default 'N' not null comment 'E for error'
)
    engine=MyISAM collate=utf8_unicode_ci;

create index message_uuid
    on cse_reminderbuffer (message_uuid);

create index reminder_uuid
    on cse_reminderbuffer (reminder_uuid);

create table cse_remindersent
(
    remindersent_id int auto_increment
        primary key,
    reminderbuffer_id int not null,
    recipients text not null,
    subject varchar(255) not null,
    message text not null,
    message_uuid varchar(55) not null,
    reminder_uuid varchar(55) not null,
    timestamp timestamp default CURRENT_TIMESTAMP not null,
    customer_id int default 0 null,
    constraint reminderbuffer_id
        unique (reminderbuffer_id)
)
    engine=MyISAM collate=utf8_unicode_ci;

create index message_uuid
    on cse_remindersent (message_uuid);

create index reminder_uuid
    on cse_remindersent (reminder_uuid);

create table cse_resets
(
    resets_id int auto_increment
        primary key,
    resetkey varchar(32) not null,
    resetemail varchar(255) default '' not null,
    user_id int not null,
    resets int unsigned default 0 not null,
    expires datetime default '0000-00-00 00:00:00' not null,
    customer_id int unsigned default 0 not null,
    constraint resetkey
        unique (resetkey)
)
    engine=MyISAM charset=latin1;

create table cse_rolodex_relations
(
    rolodex_relations_id int auto_increment
        primary key,
    rolodex_uuid varchar(45) default '' null comment 'the partie to which other parties are related',
    related text null comment 'json of related parties, both corporation and person',
    rolodex_type enum('person', 'corporation') null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null,
    constraint rolodex_uuid
        unique (rolodex_uuid)
)
    comment 'which parties are related to which, so that we can list all of the related cases together' engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_rolodex_relations (customer_id);

create index deleted
    on cse_rolodex_relations (deleted);

create table cse_rx
(
    rx_id int auto_increment
        primary key,
    rx_uuid varchar(45) default '' null,
    doctor_uuid varchar(45) default '' null,
    start_date date default '0000-00-00' null,
    end_date varchar(45) null,
    medication varchar(255) null,
    dosage varchar(45) default '' null,
    regimen varchar(255) default '' null,
    refills enum('Y', 'N') default 'N' null,
    notes text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    comment 'prescriptions' collate=utf8_unicode_ci;

create table cse_rx_track
(
    rx_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    rx_id int default 0 not null,
    rx_uuid varchar(45) default '' null,
    doctor_uuid varchar(45) default '' null,
    start_date date default '0000-00-00' null,
    end_date varchar(45) null,
    medication varchar(255) null,
    dosage varchar(45) default '' null,
    regimen varchar(255) default '' null,
    refills enum('Y', 'N') default 'N' null,
    notes text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_sent
(
    sent_id int auto_increment
        primary key,
    buffer_id int not null,
    recipients text not null,
    subject varchar(255) not null,
    message text not null,
    message_uuid varchar(15) not null,
    timestamp timestamp default CURRENT_TIMESTAMP not null,
    constraint buffer_id
        unique (buffer_id)
)
    engine=MyISAM collate=utf8_unicode_ci;

create index message_uuid
    on cse_sent (message_uuid);

create table cse_setting
(
    setting_id int auto_increment
        primary key,
    setting_uuid varchar(15) not null,
    customer_id int default 0 not null,
    category varchar(100) default '' not null,
    setting varchar(50) default '' not null,
    setting_value text not null,
    setting_type varchar(255) default '' not null comment 'color, date, choice, etc...',
    default_value varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_uuid
    on cse_setting (customer_id);

create index setting
    on cse_setting (setting);

create index setting_uuid
    on cse_setting (setting_uuid);

create table cse_setting_customer
(
    setting_customer_id int auto_increment
        primary key,
    setting_uuid varchar(15) not null,
    customer_uuid varchar(15) not null,
    attribute varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_setting_document
(
    setting_document_id int auto_increment
        primary key,
    setting_document_uuid varchar(15) not null,
    setting_uuid varchar(15) not null,
    document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index document_uuid
    on cse_setting_document (document_uuid);

create index setting_uuid
    on cse_setting_document (setting_uuid);

create table cse_setting_user
(
    setting_user_id int auto_increment
        primary key,
    setting_uuid varchar(15) not null,
    user_uuid varchar(15) not null,
    attribute varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_settlement
(
    settlement_id int auto_increment
        primary key,
    settlement_uuid varchar(15) not null,
    date_submitted date default '0000-00-00' null,
    date_settled date default '0000-00-00' not null,
    amount_of_settlement varchar(50) default '0' not null,
    future_medical enum('Y', 'N') default 'N' null,
    amount_of_fee varchar(50) default '0' not null,
    referral_info varchar(1055) default '' null,
    c_and_r varchar(255) default '' not null,
    stip varchar(255) default '' not null,
    f_and_a varchar(255) default '' not null,
    date_approved date default '0000-00-00' not null,
    pd_percent decimal(7,2) default 0.00 null,
    date_fee_received date default '0000-00-00' not null,
    attorney varchar(255) default '' not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_settlement (customer_id);

create index deleted
    on cse_settlement (deleted);

create index settlement_uuid
    on cse_settlement (settlement_uuid);

create table cse_settlement_fee
(
    settlement_fee_id int auto_increment
        primary key,
    settlement_fee_uuid varchar(15) not null,
    fee_uuid varchar(15) not null,
    settlement_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(75) default '' not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_settlement_track
(
    settlement_track_id int auto_increment
        primary key,
    user_uuid varchar(75) default '' not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    settlement_id int not null,
    settlement_uuid varchar(15) not null,
    date_submitted date default '0000-00-00' null,
    date_settled date default '0000-00-00' not null,
    amount_of_settlement varchar(50) default '0' not null,
    future_medical enum('Y', 'N') default 'N' null,
    amount_of_fee varchar(50) default '0' not null,
    c_and_r varchar(255) default '' not null,
    stip varchar(255) default '' not null,
    f_and_a varchar(255) default '' not null,
    date_approved date default '0000-00-00' not null,
    pd_percent decimal(7,2) default 0.00 null,
    date_fee_received date default '0000-00-00' not null,
    attorney varchar(255) default '' not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_settlementsheet
(
    settlementsheet_id int auto_increment
        primary key,
    settlementsheet_uuid varchar(15) default '' null,
    date_settled date default '0000-00-00' null,
    due decimal(11,2) default 0.00 null,
    data text charset utf8 null comment 'data kept as json',
    status enum('P', 'D') default 'P' null comment 'P = Pending
D = Distributed (cannot be modified)',
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    comment 'keep track of pi settlements and calculations' collate=utf8_unicode_ci;

create index settlementsheet_uuid
    on cse_settlementsheet (settlementsheet_uuid);

create table cse_settlementsheet_track
(
    settlementsheet_track_id int auto_increment
        primary key,
    user_uuid varchar(15) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    settlementsheet_id int not null,
    settlementsheet_uuid varchar(15) default '' null,
    date_settled date default '0000-00-00' null,
    due decimal(11,2) default 0.00 null,
    data text null comment 'data kept as json',
    status enum('P', 'D') default 'P' null comment 'P = Pending
D = Distributed (cannot be modified)',
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_signature
(
    signature_id int auto_increment
        primary key,
    signature_uuid varchar(15) not null,
    signature text not null,
    title varchar(255) not null,
    signs_for varchar(255) not null,
    additional_text text not null,
    image_path varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index signature_uuid
    on cse_signature (signature_uuid);

create table cse_specialties
(
    specialty_id int auto_increment
        primary key,
    specialty varchar(100) not null,
    description varchar(255) not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_surgery
(
    surgery_id int auto_increment
        primary key,
    case_uuid varchar(15) default '' null,
    surgery_info text charset utf8 null comment 'data as json',
    deleted enum('Y', 'N') charset utf8 default 'N' null,
    customer_id int default 0 null
)
    comment 'surgery info' collate=utf8_unicode_ci;

create table cse_task
(
    task_id int auto_increment
        primary key,
    task_uuid varchar(55) not null,
    task_name varchar(1055) default '' not null,
    `from` varchar(255) default '' not null,
    task_date varchar(255) default '' not null,
    task_description text not null,
    task_first_name varchar(255) default '' not null,
    task_last_name varchar(255) default '' not null,
    task_dateandtime datetime default '0000-00-00 00:00:00' not null,
    task_end_time varchar(255) default '0000-00-00 00:00:00' not null,
    full_address varchar(255) default '' not null,
    assignee varchar(65) default '' not null,
    cc varchar(45) default '' null,
    task_title varchar(1055) default '' not null,
    attachments varchar(255) default '' not null,
    task_email varchar(255) default '' not null,
    task_hour varchar(255) default '' not null,
    task_type varchar(100) default '' not null comment 'On the UI, it''s Status',
    type_of_task varchar(255) default '' null comment 'This is the actual type of task.  task_type is the "status" of the task',
    task_from varchar(250) default '' not null,
    task_priority varchar(100) default '' not null,
    end_date datetime default '0000-00-00 00:00:00' not null,
    completed_date datetime default '0000-00-00 00:00:00' not null,
    callback_date datetime default '0000-00-00 00:00:00' not null,
    callback_completed datetime default '0000-00-00 00:00:00' not null,
    color varchar(50) default 'blue' not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null,
    constraint task_uuid
        unique (task_uuid)
)
    comment 'table for task details only' engine=MyISAM collate=utf8_unicode_ci;

create index customer_id
    on cse_task (customer_id);

create index deleted
    on cse_task (deleted);

create table cse_task_document
(
    task_document_id int auto_increment
        primary key,
    task_document_uuid varchar(15) not null,
    task_uuid varchar(55) not null,
    document_uuid varchar(15) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index document_uuid
    on cse_task_document (document_uuid);

create index task_uuid
    on cse_task_document (task_uuid);

create table cse_task_track
(
    task_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    task_id int not null,
    task_uuid varchar(55) not null,
    task_name varchar(255) not null,
    task_date varchar(255) not null,
    task_description text not null,
    task_first_name varchar(255) not null,
    task_last_name varchar(255) not null,
    task_dateandtime datetime not null,
    task_end_time varchar(255) not null,
    full_address varchar(255) not null,
    assignee varchar(65) not null,
    cc varchar(45) default '' null,
    task_title varchar(255) not null,
    attachments varchar(255) not null,
    task_email varchar(255) not null,
    task_hour varchar(255) not null,
    task_type varchar(100) default '' not null comment 'On the UI, it''s Status',
    type_of_task varchar(255) default '' null comment 'This is the actual type of task.  task_type is the "status" of the task',
    task_from varchar(250) not null,
    task_priority varchar(100) not null,
    end_date datetime not null,
    completed_date datetime not null,
    callback_date datetime not null,
    callback_completed datetime not null,
    color varchar(50) default 'blue' not null,
    customer_id int not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index operation
    on cse_task_track (operation);

create index task_uuid
    on cse_task_track (task_uuid);

create index user_uuid
    on cse_task_track (user_uuid);

create table cse_task_type
(
    task_type_id int auto_increment
        primary key,
    task_type varchar(255) default '' null,
    last_change_user varchar(45) default '' null,
    last_change_date datetime default '0000-00-00 00:00:00' null,
    deleted enum('Y', 'N') default 'N' null
)
    collate=utf8_unicode_ci;

create table cse_task_user
(
    task_user_id int auto_increment
        primary key,
    task_user_uuid varchar(45) not null,
    task_uuid varchar(15) not null,
    user_uuid varchar(45) not null,
    thread_uuid varchar(255) default '' null,
    type enum('from', 'to', 'cc', 'bcc') default 'to' not null,
    read_status enum('Y', 'N') default 'N' not null,
    read_date datetime default '0000-00-00 00:00:00' not null,
    action enum('reply', 'forward') not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index deleted
    on cse_task_user (deleted);

create index task_uuid
    on cse_task_user (task_uuid);

create index type
    on cse_task_user (type);

create index user_uuid
    on cse_task_user (user_uuid);

create table cse_thread
(
    thread_id int auto_increment
        primary key,
    thread_uuid varchar(155) not null,
    dateandtime datetime not null,
    `from` varchar(255) not null,
    subject varchar(255) not null,
    customer_id int default 0 not null,
    deleted enum('Y', 'N') default 'N' not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index thread_uuid
    on cse_thread (thread_uuid);

create table cse_thread_chat
(
    thread_chat_id int auto_increment
        primary key,
    thread_chat_uuid varchar(15) not null,
    thread_uuid varchar(155) not null,
    chat_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(15) not null comment 'chat uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index chat_uuid
    on cse_thread_chat (chat_uuid);

create index thread_uuid
    on cse_thread_chat (thread_uuid);

create table cse_thread_message
(
    thread_message_id int auto_increment
        primary key,
    thread_message_uuid varchar(15) not null,
    thread_uuid varchar(155) not null,
    message_uuid varchar(155) not null,
    message_id int default 0 null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index message_id
    on cse_thread_message (message_id);

create index message_uuid
    on cse_thread_message (message_uuid);

create index thread_uuid
    on cse_thread_message (thread_uuid);

create table cse_thread_task
(
    thread_task_id int auto_increment
        primary key,
    thread_task_uuid varchar(15) not null,
    thread_uuid varchar(155) not null,
    task_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index task_uuid
    on cse_thread_task (task_uuid);

create index thread_uuid
    on cse_thread_task (thread_uuid);

create table cse_trigger
(
    trigger_id int auto_increment
        primary key,
    trigger_uuid varchar(15) default '' null,
    workflow_uuid varchar(15) default '' null,
    action enum('date', 'status', 'letter', 'form', 'task', 'event', '') default '' null,
    operation enum('event', 'task', 'message', '') default 'task' null,
    assignee varchar(255) default '' null comment 'KASE_ATTY = Attorney
KASE_SATTY = Supervising Attorney
KASE_COORD = Coordinator',
    trigger_time decimal(7,1) default 0.0 null,
    trigger_interval varchar(45) default '' null,
    trigger_actual varchar(45) default '' null,
    `trigger` enum('B', 'A', '') default '' null comment 'B = BEFORE
A = AFTER',
    trigger_description varchar(1055) default '' null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    collate=utf8_unicode_ci;

create index deleted
    on cse_trigger (deleted);

create index trigger_uuid
    on cse_trigger (trigger_uuid);

create index workflow_uuid
    on cse_trigger (workflow_uuid);

create table cse_user
(
    user_id int auto_increment
        primary key,
    user_uuid varchar(32) charset latin1 default '' not null,
    customer_id int default 0 not null,
    cis_id int default 0 not null,
    cis_uid int default 0 not null,
    user_type int default 0 not null,
    user_name varchar(255) null,
    user_logon varchar(50) charset latin1 default '' not null,
    user_first_name varchar(255) null,
    user_last_name varchar(255) null,
    user_email varchar(255) charset latin1 default '' not null,
    user_cell varchar(15) default '' null,
    nickname varchar(255) null,
    pwd varchar(255) charset latin1 default '' not null comment 'hash password',
    level varchar(50) charset latin1 default '' not null,
    job varchar(100) charset latin1 default '' not null,
    status varchar(100) charset latin1 default '' not null,
    personal_calendar enum('Y', 'N') default 'N' not null,
    access_token varchar(555) charset latin1 default '' not null,
    any_time enum('Y', 'N') default 'N' null,
    day_start varchar(7) charset latin1 default '08:00AM' not null comment '24:00 time format',
    day_end varchar(7) charset latin1 default '05:00PM' not null,
    days_of_week varchar(15) charset latin1 null,
    dow_times varchar(100) charset latin1 null,
    sess_id varchar(50) charset latin1 null,
    dateandtime datetime default '0000-00-00 00:00:00' not null,
    ip_address varchar(50) charset latin1 default '0.0.0.0' not null,
    calendar_color varchar(7) default '' null,
    deleted enum('Y', 'N') charset latin1 default 'N' not null,
    activated enum('Y', 'N') default 'Y' null,
    imei_number varchar(255) null,
    default_attorney enum('Y', 'N') default 'N' null,
    rate varchar(45) null,
    tax varchar(45) null,
    adhoc varchar(1055) default '' null comment 'catch-all for permissions, checkbox stuff',
    constraint user_uuid
        unique (user_uuid)
)
    collate=utf8_unicode_ci;

create index cus_id
    on cse_user (customer_id);

create index nickname
    on cse_user (nickname);

create table cse_user_address
(
    user_address_id int auto_increment
        primary key,
    user_address_uuid varchar(15) not null,
    user_uuid int not null,
    address_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint user_address_uuid
        unique (user_address_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_blocked
(
    user_blocked_id int auto_increment
        primary key,
    user_blocked_uuid varchar(15) not null,
    user_uuid varchar(15) not null,
    blocked_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    constraint user_blocked_uuid
        unique (user_blocked_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_calendar
(
    user_calendar_id int auto_increment
        primary key,
    user_calendar_uuid varchar(15) not null,
    user_uuid varchar(15) not null,
    calendar_uuid varchar(15) not null comment 'this is the user_uuid of another user, showing assign',
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index calendar_uuid
    on cse_user_calendar (calendar_uuid);

create index user_uuid
    on cse_user_calendar (user_uuid);

create table cse_user_config
(
    user_config_id int auto_increment
        primary key,
    user_config_uuid varchar(15) not null,
    user_uuid int not null,
    config_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint user_config_uuid
        unique (user_config_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_contact
(
    user_contact_id int auto_increment
        primary key,
    user_contact_uuid varchar(15) not null,
    user_uuid int not null,
    contact_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint user_contact_uuid
        unique (user_contact_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_corporation
(
    user_corporation_id int auto_increment
        primary key,
    user_corporation_uuid varchar(15) not null,
    user_uuid varchar(155) not null,
    corporation_uuid varchar(155) not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null,
    `delete` enum('N', 'Y') default 'N' not null,
    customer_id int default 0 not null,
    constraint user_corporation_uuid
        unique (user_corporation_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_document
(
    user_document_id int auto_increment
        primary key,
    user_document_uuid varchar(15) not null,
    user_uuid int not null,
    document_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint user_document_uuid
        unique (user_document_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_email
(
    user_email_id int auto_increment
        primary key,
    user_email_uuid varchar(15) not null,
    user_uuid varchar(15) not null,
    email_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    constraint user_email_uuid
        unique (user_email_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_event
(
    user_event_id int auto_increment
        primary key,
    user_event_uuid varchar(15) not null,
    user_uuid int not null,
    event_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint user_event_uuid
        unique (user_event_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_job
(
    user_job_id int auto_increment
        primary key,
    user_job_uuid varchar(15) not null,
    user_uuid varchar(155) not null,
    job_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    constraint user_job_uuid
        unique (user_job_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_person
(
    user_person_id int auto_increment
        primary key,
    user_person_uuid varchar(15) not null,
    user_uuid int not null,
    person_uuid int not null,
    attribute_1 varchar(255) not null,
    attribute_2 varchar(255) not null,
    last_updated_date varchar(255) not null,
    last_update_user varchar(255) not null,
    `delete` enum('0', '1') default '0' not null,
    customer_id int not null,
    constraint user_person_uuid
        unique (user_person_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_signature
(
    user_signature_id int auto_increment
        primary key,
    user_signature_uuid varchar(15) not null,
    user_uuid varchar(15) not null,
    signature_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(255) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    constraint user_signature_uuid
        unique (user_signature_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_task
(
    user_task_id int auto_increment
        primary key,
    user_task_uuid varchar(15) not null,
    user_uuid varchar(15) not null,
    task_uuid varchar(15) not null,
    attribute varchar(20) not null,
    last_updated_date datetime default '0000-00-00 00:00:00' not null,
    last_update_user varchar(15) not null comment 'user uuid',
    deleted enum('Y', 'N') default 'N' not null,
    customer_id int not null,
    constraint user_task_uuid
        unique (user_task_uuid)
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_user_track
(
    user_track_id int auto_increment
        primary key,
    user_uuid varchar(155) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    user_id int not null,
    customer_id int not null,
    cis_id int not null,
    cis_uid int not null,
    user_type int default 0 not null,
    user_name varchar(255) not null,
    user_first_name varchar(100) not null,
    user_last_name varchar(100) not null,
    user_email varchar(255) not null,
    nickname varchar(4) not null,
    pwd varchar(255) not null comment 'hash password',
    level varchar(20) not null,
    job varchar(100) not null,
    status varchar(100) not null,
    access_token varchar(255) charset latin1 default '' not null,
    day_start varchar(7) default '08:00AM' not null comment '24:00 time format',
    day_end varchar(7) default '05:00PM' not null,
    days_of_week varchar(15) null,
    dow_times varchar(100) null,
    sess_id varchar(100) null,
    dateandtime datetime default '0000-00-00 00:00:00' not null,
    ip_address varchar(100) default '0.0.0.0' not null,
    calendar_color varchar(7) default '' null,
    deleted enum('Y', 'N') default 'N' not null,
    adhoc varchar(1055) default '' null comment 'catch-all for permissions, checkbox stuff',
    imei_number varchar(255) null
)
    engine=MyISAM collate=utf8_unicode_ci;

create table cse_userlogin
(
    userlogin_id int auto_increment
        primary key,
    user_uuid varchar(155) not null,
    user_name varchar(50) default '' not null,
    status enum('IN', 'OUT', 'BREAK', 'LUNCH', 'OUTT', 'INN') default 'IN' not null,
    ip_address varchar(100) null,
    dateandtime datetime default '0000-00-00 00:00:00' not null,
    login_date date not null comment 'just the date, for search purposes',
    timestamp timestamp default CURRENT_TIMESTAMP not null,
    customer_id int default 0 not null
)
    comment 'user status' engine=MyISAM charset=latin1;

create index dateandtime
    on cse_userlogin (dateandtime);

create index login_date
    on cse_userlogin (login_date);

create index status
    on cse_userlogin (status);

create table cse_venue
(
    venue_id int not null
        primary key,
    venue_uuid varchar(15) not null,
    venue varchar(255) not null,
    venue_abbr varchar(3) not null,
    address1 varchar(255) not null,
    address2 varchar(255) not null,
    city varchar(100) not null,
    zip varchar(5) not null,
    phone varchar(25) not null,
    presiding varchar(255) not null,
    constraint venue_abbr
        unique (venue_abbr),
    constraint venue_uuid
        unique (venue_uuid)
)
    comment 'Division of Workers'' Compensation - Office locations' engine=MyISAM collate=utf8_unicode_ci;

create index venue
    on cse_venue (venue);

create table cse_vservice
(
    vservice_id int auto_increment
        primary key,
    vservice_uuid varchar(45) null,
    type varchar(45) default '' not null,
    name varchar(45) default '' not null,
    description varchar(255) default '' null,
    attachments varchar(255) default '' not null,
    contact_person varchar(255) default '' not null,
    email varchar(255) default '' not null,
    company_site varchar(255) default '' not null,
    phone varchar(45) default '' not null,
    fax varchar(45) default '' not null,
    full_address varchar(255) default '' not null,
    deleted enum('Y', 'N') default 'N' null
)
    comment 'services offered to customers';

create index vservice_uuid
    on cse_vservice (vservice_uuid);

create table cse_webmail
(
    webmail_id int auto_increment
        primary key,
    webmail_uuid varchar(15) default '' null,
    message_id varchar(255) default '' null,
    user_id int default 0 null,
    message_date datetime default '0000-00-00 00:00:00' null,
    `to` varchar(255) default '' null,
    `from` varchar(255) default '' null,
    subject varchar(255) default '' null,
    message longtext null,
    attachments varchar(1050) default '' null,
    customer_id int default 0 null,
    deleted enum('Y', 'N') default 'N' null
);

create index message_id
    on cse_webmail (message_id);

create index webmail_uuid
    on cse_webmail (webmail_uuid);

create table cse_work_history
(
    work_history_id int auto_increment
        primary key,
    work_history_uuid varchar(15) collate utf8_unicode_ci default '' null,
    work_history_info text null,
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null,
    case_id int null
);

create table cse_workflow
(
    workflow_id int auto_increment
        primary key,
    workflow_uuid varchar(15) default '' null,
    workflow_date timestamp default CURRENT_TIMESTAMP null,
    case_type varchar(45) default '' null,
    workflow_number varchar(45) default '' null,
    description varchar(1055) default '' null,
    active enum('Y', 'N') default 'Y' null,
    activated_by varchar(30) default '' null comment 'user_uuid from ikase.cse_user',
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    collate=utf8_unicode_ci;

create index activated_by
    on cse_workflow (activated_by);

create index deleted
    on cse_workflow (deleted);

create index workflow_uuid
    on cse_workflow (workflow_uuid);

create table cse_workflow_track
(
    workflow_track_id int auto_increment
        primary key,
    user_uuid varchar(45) not null,
    user_logon varchar(30) not null,
    operation varchar(30) not null,
    time_stamp timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    workflow_id int not null,
    workflow_uuid varchar(15) default '' null,
    workflow_date datetime default '0000-00-00 00:00:00' not null,
    case_type varchar(45) default '' null,
    workflow_number varchar(45) default '' null,
    description varchar(1055) default '' null,
    active enum('Y', 'N') default 'Y' null,
    activated_by varchar(30) default '' null comment 'user_uuid from ikase.cse_user',
    deleted enum('Y', 'N') default 'N' null,
    customer_id int default 0 null
)
    engine=MyISAM collate=utf8_unicode_ci;

create index operation
    on cse_workflow_track (operation);

create index user_uuid
    on cse_workflow_track (user_uuid);

create index workflow_uuid
    on cse_workflow_track (workflow_uuid);

create table examples
(
    text text not null,
    state enum('0', '1', 'spam') default '1' not null
)
    engine=MyISAM;

create index comment_approved
    on examples (state);

create table knowledge_base
(
    ngram varchar(10) not null,
    belongs varchar(10) not null,
    repite int not null,
    percent float not null,
    primary key (ngram, belongs)
)
    engine=MyISAM;

create index repite
    on knowledge_base (repite);

create table tbl_tipoftheday
(
    tipoftheday_id int auto_increment
        primary key,
    tipoftheday text null
);

create table zip_code
(
    id int(11) unsigned auto_increment
        primary key,
    zip_code varchar(5) collate utf8_bin not null,
    city varchar(50) collate utf8_bin null,
    county varchar(50) collate utf8_bin null,
    state_name varchar(50) collate utf8_bin null,
    state_prefix varchar(2) collate utf8_bin null,
    area_code varchar(3) collate utf8_bin null,
    time_zone varchar(50) collate utf8_bin null,
    lat float not null,
    lon float not null
)
    engine=MyISAM charset=latin1;

create index state
    on zip_code (state_prefix);

create index zip_code
    on zip_code (zip_code);
