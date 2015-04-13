<?php
namespace Db;

class Table
{
    protected $_schema;
    //表名
    protected $_name = '';
    protected $_DbClusterName = '';
    //Db\Mysqli的实例
    public $_db;
    protected $_dataFilter   = array();
    protected $_dataValidate = array();

    /**
     * [setConfig description]
     * @param array $config [description]
     */
    public static function setConfig(array $config)
    {
        Mysqli::setConfig($config);
    }
    //初始化方法 ,DbClusterName 是数据库集群名称
    //  实例化时指定集群名，使用指定的集群名称
    //  实例化时未指定集群名，使用子类Model中设置的集群名称
    //  实例化时未指定集群名，子类未指定集群名称，使用default
    public function __construct($DbClusterName = null)
    {
        if($DbClusterName === null) {
            if(!empty($this->_DbClusterName)) {
                $DbClusterName = $this->_DbClusterName;
            } else {
                $DbClusterName = 'default';
            }
        }
        $this->_db = Mysqli::getInstance($DbClusterName);
    }
    public function __call($method, $arg)
    {
        $ret = call_user_func_array(array($this->_db, $method), $arg);
        if(empty($ret)) {
            return $this;
        } else {
            return $ret;
        }
    }
    //执行query语句得到相关结果集 主要用于自定义SQL的查询操作
    public function query($sql, $bindData)
    {
        return $this->_db->rawQuery($sql, $bindData);
    }
    public function findAll($fields = array(), $numRows = 100)
    {
        return $this->_db->get($this->_getDbTableName(), $numRows, $fields);
    }
    public function findOne($fields = array())
    {
        $rs = $this->_db->get($this->_getDbTableName(), 1, $fields);
        if (is_array($rs) && count($rs) > 0) {
            return $rs[0];
        } else {
            return array();
        }
    }
    public function getCount()
    {
        return $this->_db->getValue($this->_getDbTableName(), "count(*)");
    }
    
    //动态生成表名称
    protected function _getDbTableName()
    {
        return ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
    }

    //添加记录,会返回插入的id
    public function insert(array $data)
    {
        return $this->_db->insert($this->_getDbTableName(), $data);
    }

    //修改记录 这个更新与表的主键没有关系,用于多条记录的更新.
    public function update(array $data)
    {
        return $this->_db->update($this->_getDbTableName(), $data);
    }
    //删除记录
    public function delete($limit = null)
    {
         return $this->_db->delete($this->_getDbTableName(), $limit);
    }
}
