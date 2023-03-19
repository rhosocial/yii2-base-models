create table if not exists public.entity
(
    guid          uuid                                                                    not null
        constraint entity_pk
            primary key,
    id            varchar(16)                                                             not null
        constraint entity_id_unique
            unique,
    content       varchar(255) default ''::character varying                              not null,
    ip            varchar(39)  default ''::character varying                              not null,
    ip_type       smallint     default 4                                                  not null,
    created_at    timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at    timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    expired_after bigint       default 0                                                  not null
);

alter table public.entity
    owner to "user";

create table if not exists public.operator_entity
(
    guid          uuid                                                                    not null
        constraint operator_entity_pk
            primary key,
    id            varchar(16)                                                             not null
        constraint operator_entity_unique_pk
            unique,
    content       varchar(255) default ''::character varying                              not null,
    ip            varchar(39)  default ''::character varying                              not null,
    ip_type       smallint     default 4                                                  not null,
    created_at    timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at    timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    expired_after bigint       default 0                                                  not null,
    operator_guid uuid                                                                    not null
);

alter table public.operator_entity
    owner to "user";

create table if not exists public."user"
(
    guid                 uuid                                                                    not null
        constraint user_pk
            primary key,
    id                   bigint                                                                  not null
        constraint user_id_unique
            unique,
    pass_hash            varchar(80)  default ''::character varying                              not null,
    created_at           timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at           timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    expired_after        bigint       default 0                                                  not null,
    ip                   varchar(39)  default ''::character varying                              not null,
    ip_type              smallint     default 4                                                  not null,
    auth_key             varchar(40)
        constraint user_auth_key_unique
            unique,
    access_token         varchar(40)
        constraint user_access_token_unique
            unique,
    password_reset_token varchar(40)
        constraint user_password_reset_token_unique
            unique,
    status               smallint     default 0                                                  not null,
    source               varchar(255) default ''::character varying                              not null
);

alter table public."user"
    owner to "user";

create table if not exists public.user_additional_account
(
    guid           uuid                                                                   not null
        constraint user_additional_account_pk
            primary key,
    user_guid      uuid                                                                   not null
        constraint user_additional_account__user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    id             varchar(8)  default 0                                                  not null,
    pass_hash      varchar(80) default ''::character varying                              not null,
    separate_login smallint    default 0                                                  not null,
    content        smallint    default 0                                                  not null,
    source         smallint    default 0                                                  not null,
    description    text        default ''::text                                           not null,
    ip             varchar(39) default ''::character varying                              not null,
    ip_type        smallint    default 4                                                  not null,
    confirmed      smallint    default 0                                                  not null,
    confirmed_at   timestamp   default '1970-01-01 00:00:00'::timestamp without time zone not null,
    created_at     timestamp   default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at     timestamp   default '1970-01-01 00:00:00'::timestamp without time zone not null
);

alter table public.user_additional_account
    owner to "user";

create index if not exists user_additional_account_user_guid_index
    on public.user_additional_account (user_guid);

create table if not exists public.user_comment
(
    guid         uuid                                                                    not null
        constraint user_comment_pk
            primary key,
    id           varchar(255)                                                            not null,
    parent_guid  uuid,
    user_guid    uuid                                                                    not null
        constraint user_comment__user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    post_guid    uuid                                                                    not null,
    content      varchar(255) default ''::character varying                              not null,
    created_at   timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at   timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    ip           varchar(39)  default ''::character varying                              not null,
    ip_type      smallint     default 4                                                  not null,
    confirmed    smallint     default 0                                                  not null,
    confirmed_at timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    confirm_code varchar(8)   default ''::character varying                              not null
);

alter table public.user_comment
    owner to "user";

create table if not exists public.user_email
(
    guid         uuid                                                                    not null
        constraint user_email_pk
            primary key,
    user_guid    uuid                                                                    not null
        constraint user_email__user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    id           varchar(8)                                                              not null,
    email        varchar(255) default ''::character varying                              not null,
    type         smallint     default 4                                                  not null,
    created_at   timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at   timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    confirmed    smallint     default 0                                                  not null,
    confirmed_at timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    confirm_code varchar(20)  default ''::character varying                              not null,
    description  text         default ''::text                                           not null
);

alter table public.user_email
    owner to "user";

create table if not exists public.user_meta
(
    guid      uuid                                       not null
        constraint user_meta_pk
            primary key,
    user_guid uuid                                       not null
        constraint user_meta__user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    key       varchar(190) default ''::character varying not null,
    value     text         default ''::text              not null,
    constraint user_meta_unique
        unique (key, user_guid)
);

alter table public.user_meta
    owner to "user";

create table if not exists public.user_post
(
    guid       uuid                                                                   not null
        constraint user_post_pk
            primary key,
    user_guid  uuid                                                                   not null
        constraint user_post__user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    id         varchar(255)                                                           not null
        constraint user_post_unique
            unique,
    content    text        default ''::text                                           not null,
    ip         varchar(39) default ''::character varying                              not null,
    ip_type    smallint    default 4                                                  not null,
    created_at timestamp   default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at timestamp   default '1970-01-01 00:00:00'::timestamp without time zone not null
);

alter table public.user_post
    owner to "user";

create index if not exists user_post_user_guid_index
    on public.user_post (user_guid);

create table if not exists public.user_relation
(
    guid        uuid                                                                     not null
        constraint user_relation_pk
            primary key,
    id          varchar(8)                                                               not null,
    user_guid   uuid                                                                     not null
        constraint user_relation__user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    remark      varchar(255)                                                             not null,
    other_guid  uuid                                                                     not null
        constraint user_relation__other_user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    type        smallint      default 0                                                  not null,
    favorite    smallint      default 0                                                  not null,
    ip          varchar(39)   default ''::character varying                              not null,
    ip_type     smallint      default 4                                                  not null,
    created_at  timestamp     default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at  timestamp     default '1970-01-01 00:00:00'::timestamp without time zone not null,
    groups      varchar(2304) default ''::character varying                              not null,
    description text          default ''::text                                           not null,
    constraint user_relation_unique
        unique (user_guid, other_guid),
    constraint user_relation_user_unique
        unique (id, user_guid)
);

alter table public.user_relation
    owner to "user";

create index if not exists user_relation_other_guid_index
    on public.user_relation (other_guid);

create table if not exists public.user_relation_group
(
    guid        uuid                                                                    not null
        constraint user_relation_group_pk
            primary key,
    id          varchar(4)                                                              not null,
    user_guid   uuid                                                                    not null
        constraint user_relation_group__user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    content     varchar(255) default ''::character varying                              not null,
    updated_at  timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    created_at  timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    description text         default ''::text                                           not null,
    constraint user_relation_group_unique
        unique (user_guid, id)
);

alter table public.user_relation_group
    owner to "user";

create index if not exists user_relation_group_user_guid_index
    on public.user_relation_group (user_guid);

create table if not exists public.user_single_relation
(
    guid        uuid                                                                     not null
        constraint user_single_relation_pk
            primary key,
    id          varchar(8)                                                               not null,
    user_guid   uuid                                                                     not null
        constraint user_single_relation__user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    remark      varchar(255)  default ''::character varying                              not null,
    other_guid  uuid                                                                     not null
        constraint user_single_relation__other_user_guid_fk
            references public."user"
            on update cascade on delete cascade,
    favorite    smallint      default 0                                                  not null,
    ip          varchar(39)   default ''::character varying                              not null,
    ip_type     smallint      default 4                                                  not null,
    created_at  timestamp     default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at  timestamp     default '1970-01-01 00:00:00'::timestamp without time zone not null,
    groups      varchar(2304) default ''::character varying                              not null,
    description text          default ''::text                                           not null,
    constraint user_single_relation_unique
        unique (user_guid, other_guid),
    constraint user_single_relation_user_unique
        unique (id, user_guid)
);

alter table public.user_single_relation
    owner to "user";

create index if not exists user_single_relation_other_guid_index
    on public.user_single_relation (other_guid);

create table if not exists public.entity_ai
(
    guid       uuid                                                                    not null
        constraint entity_ai_pk
            primary key,
    id         serial
        constraint entity_ai_unique
            unique,
    content    varchar(255) default ''::character varying                              not null,
    ip         varchar(39)  default ''::character varying                              not null,
    ip_type    smallint     default 4                                                  not null,
    created_at timestamp    default '1970-01-01 00:00:00'::timestamp without time zone not null,
    updated_at timestamp    default '1970-01-01 00:00:00'::timestamp without time zone
);

alter table public.entity_ai
    owner to "user";
