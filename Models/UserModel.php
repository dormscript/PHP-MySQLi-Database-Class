<?php
namespace Xz\Db;

class UserModel extends Table
{
    public $_schema  = 'gongchanginfo';
    public $_name    = 'gc_company';
    public $_primary = 'cid';
    public $_DbClusterName = 'default';
}
