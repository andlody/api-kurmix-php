<?php

 /** @Entity @Table('user') */
class User extends Model {
    
    /** @Id @Type('int') */
    public $id;

    /** @Column('name') @Type('varchar(12)') @Default('holi') */
    public $nombre;

    /** @Column('pass') @Type('varchar(12)') @Null */
    public $contrasena;
}