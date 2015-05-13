drop table if exists authorities cascade;
drop table if exists accounts cascade;
drop table if exists tokens cascade;
drop table if exists owners cascade;

create table authorities (
	 authority text not null primary key
);

insert into authorities values('github');

create table accounts (
	 account uuid not null default uuid_generate_v4() primary key
);

create table tokens (
	 token text not null primary key
	,account uuid not null references accounts on update cascade on delete cascade
	,authority text not null references authorities on update cascade on delete cascade
	,oauth jsonb
);

create table owners (
	 account uuid not null references accounts on update cascade on delete cascade
	,authority text not null references authorities on update cascade on delete cascade
	,login text not null
	,owner jsonb
	,primary key (account,authority)
	,unique (login,authority)
);
