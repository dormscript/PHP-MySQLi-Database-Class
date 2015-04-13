<?php
namespace Xz\Db;

class Table
{
    protected $_schema;
    //表名
    protected $_name = '';
    //主键
    protected $_primary = '';
    protected $_DbClusterName = '';
    //Xz\Db\Mysqli的实例
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

    public function findCol($where, $field, $order = '', $limit = 100)
    {

    }

    /**
     * 获取的结构以传入的字段作为数组的key
     * @param  [type]  $where [description]
     * @param  [type]  $key   需要作为key的键
     * @param  [type]  $field [description]
     * @param  string  $order [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function fetchByKey($where, $key = null, $fields = array(), $order = '', $limit = 100)
    {

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

    //-------------------------------------过滤与校验直接操作，封装更新与插入 省去验证----------------------\\
    /**
     * 插入单条记录或更新单条记录,如果把主键写在数组中就是更新操作,否则是插入操作.
     * @param array $data
     * @param int $opFlag 0:默认依据数组中是否有primary key区分insert与update,1:insert,2:update
     * @param bool $ifFilter: true:默认过滤,false:不过滤
     * @param bool $ifValidate: true:默认校验,false:不校验
     * @return int id
     */
    public function save($data, $opFlag = 0, $ifFilter = true, $ifValidate = true)
    {

    }

    /**
     * 插入单条记录或更新单条记录,如果把主键写在数组中就是更新操作,否则是插入操作.
     * @param array $data
     * @param int $opFlag 0:默认依据数组中是否有primary key区分insert与update, 1:insert, 2:update
     * @return bool true:当前是insert操作 false:当前是update操作.
     */
    private function judgeIfInsert($data, $opFlag)
    {

    }
    /**
     * 校验操作
     * @param array $data
     * @param int $opFlag 0:默认依据数组中是否有primary key区分insert与update,1:insert,2:update
     * @param array $ifFilter true:默认过滤, false:不过滤
     * @return $data
     */
    public function validateData($data, $opFlag = 0, $ifFilter = true)
    {
        $ifInsert = $this->judgeIfInsert($data, $opFlag);
        if ($ifFilter) {
            //数据过滤
            $filterObj = Xz_Filter::getInstance();
            $data      = $filterObj->filter($data, $this->_dataFilter);
        }
        //数据校验
        $validateObj = Xz_Validate::getInstance();
        $validateObj->validate($data, $this->_dataValidate, $ifInsert);
        return $data;
    }

    /**
     * 过滤操作
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
        //数据过滤
        $filterObj = Xz_Filter::getInstance();
        return $filterObj->filter($data, $this->_dataFilter);
    }

    public function bindValidate($arr)
    {
        if (!is_array($arr)) {
            return;
        }
        foreach ($arr as $k => $v) {
            if (isset($this->_dataValidate[$k])) {
                $this->_dataValidate[$k] = array_merge($this->_dataValidate[$k], $v);
            } else {
                $this->_dataValidate[$k] = $v;
            }
        }
    }

    /**
     * 去除过滤规则,数组结构与声明时的一样.
     * @param array $arr,要绑定的新过滤.
     * e.g.
     * array(
     *    'id'  => array(
     *        'gt0','lt0',
     *   ),
     * @return void
     */
    public function unbindValidate($arr)
    {
        if (!is_array($arr)) {
            return;
        }
        // return $arr;
        foreach ($arr as $k => $v) {

            if (isset($this->_dataValidate[$k])) {
                $v                       = array_flip($v);
                $this->_dataValidate[$k] = array_diff_key($this->_dataValidate[$k], $v);
                if (empty($this->_dataValidate[$k])) {
                    unset($this->_dataValidate[$k]);
                }
            }
        }
        return $this->_dataValidate;
    }

    /**
     * 绑定新的过滤规则,数组结构与声明时的一样.
     * @param array $arr,要绑定的新过滤.
     * e.g.
     *      array(
     *     'id'   => array(
     *            'int',
     *            'upper'
     *         ),
     *   }
     *   或
     *   array(
     *     'id'   => 'upper',
     *   }
     * @return void
     */
    public function bindFilter($arr)
    {
        if (!is_array($arr)) {
            return;
        }
        foreach ($arr as $k => $v) {
            if (isset($this->_dataFilter[$k])) {
                $v                     = (array) $v;
                $this->_dataFilter[$k] = (array) $this->_dataFilter[$k];
                $this->_dataFilter[$k] = array_unique(array_merge($this->_dataFilter[$k], $v));
            } else {
                $this->_dataFilter[$k] = $v;
            }
        }
    }

    /**
     * 去除过滤规则,数组结构与声明时的一样.
     * @param array $arr,要绑定的新过滤.
     * e.g.
     *      array(
     *     'id'   => array(
     *            'int',
     *            'upper'
     *         ),
     *   }
     *   或
     *   array(
     *     'id'   => 'upper',
     *   }
     * @return void
     */
    public function unbindFilter($arr)
    {
        if (!is_array($arr)) {
            return;
        }
        foreach ($arr as $k => $v) {
            if (isset($this->_dataFilter[$k])) {
                $v                     = (array) $v;
                $this->_dataFilter[$k] = (array) $this->_dataFilter[$k];
                $this->_dataFilter[$k] = array_diff($this->_dataFilter[$k], $v);
                if (empty($this->_dataFilter[$k])) {
                    unset($this->_dataFilter[$k]);
                }
            }
        }
    }

    /**
     * 绑定新的过滤规则,数组结构与声明时的一样.
     * @param array $arr,要绑定的新过滤.
     * e.g.
     *     array(
     *      'id', 'title'
     *    ),
     * @return void
     */
    public function setRequire($arr)
    {
        if (!is_array($arr)) {
            return;
        }
        foreach ($arr as $v) {
            if (isset($this->_dataValidate[$v])) {
                $this->_dataValidate[$v]['require'] = true;
            } else {
                $this->_dataValidate[$v]            = array();
                $this->_dataValidate[$v]['require'] = true;
            }
        }
    }

    /**
     * 去除过滤规则,数组结构与声明时的一样.
     * @param array $arr,要绑定的新过滤.
     * e.g.
     * array(
     *         'id', 'title'
     *   ),
     * @return void
     */
    public function unsetRequire($arr)
    {
        if (!is_array($arr)) {
            return;
        }
        foreach ($arr as $v) {
            if (isset($this->_dataValidate[$v]['require'])) {
                unset($this->_dataValidate[$v]['require']);
            }
        }
    }
 
}
