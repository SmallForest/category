<?php

namespace model;

use Exception;
use think\Model;
use think\facade\Db;

class category extends Model
{

    /**
     * 设置数据表名称
     * @var string
     */
    protected $table = 'category_optimization';

    /**
     * 增加单个分类
     *
     * @param int $pid
     * @param string $name
     * @return int
     */
    public function add(int $pid, string $name): int
    {
        $id = $this->insertGetId([
            "pid" => $pid,
            "name" => $name,
            "is_delete" => 0,
            "level_str" => "",
        ]);
        $this->where('id', $id)->update(['level_str' => $this->createLevelStr($id)]);
        return (int)$id;
    }

    /**
     * 定义生成level_str的规则，按照pid规则倒序拼接L字符串
     * @param int $id 当前记录的ID
     */
    private function createLevelStr(int $id): string
    {
        //'|'非常重要 eg:L10|
        $str = 'L' . $id . '|';
        //获取pid
        $pid = $this->getPID($id);
        //获取pid的LevelStr,并和当前的str拼接一起返回
        return $this->getLevelStr($pid) . $str;
    }

    /**
     * 获取父级ID
     * @param int $id
     * @return int
     */
    private function getPID(int $id): int
    {
        return (int)$this->where('id', $id)->where('is_delete', 0)->value('pid');
    }

    /**
     * 获取level_str字符串
     * @param int $id
     * @return string
     */
    private function getLevelStr(int $id): string
    {
        //存在父级ID等于0的记录，这个时候直接返回空字符串即可，不用查询数据库
        if ($id == 0) return "";
        return (string)$this->where('id', $id)->where('is_delete', 0)->value('level_str');
    }

    /**
     * 删除层级id所有子级，包括孙子，重孙子不包括自己
     * @param int $id
     */
    public function deleteSons(int $id)
    {
        $this->where('is_delete', 0)
            ->where('id', '<>', $id)
            ->where('level_str', 'like', '%L' . $id . '|%')
            ->update(["is_delete" => 1]);
    }

    /**
     * 父级修改了自己的pid,不会影响所有子级的pid
     * 但是会影响所有子级的level_str
     * 实现思路就是进行替换
     * @param string $oldStr 变动前的str
     * @param string $newStr 变动后的str
     * @param int $id 需要忽略的ID
     */
    private function modifySonLevelStr(string $oldStr, string $newStr, int $id)
    {
        //update category_optimization set `level_str`=replace(`level_str`,'L4|L1|','L4|') where `level_str` like '%L4|L1|%' and `is_delete`=0 ;
        $sql = "update `{$this->table}` set `level_str`=replace(`level_str`,'{$oldStr}','{$newStr}') where `level_str` like '%{$oldStr}%' " .
            "and `is_delete`=0 and id <> {$id}";
        Db::execute($sql);
    }

    /**
     * 修改父级ID
     *
     * @param integer $id 层级ID
     * @param integer $pid 父级修改后的ID
     * @return void
     */
    public function changePid(int $id, int $pid)
    {
        //增加限制条件：不能将自己的子级设置为自己的父级
        $parents_ids = $this->getParentsIds($pid);
        if (in_array($id, $parents_ids)) {
            throw new Exception("禁止将父级直接切到子级下");
        }
        //step 1 更新父级ID
        $this->where('id', $id)->update(['pid' => $pid]);
        //step 2 获取原来的level_str
        $oldStr = $this->getLevelStr($id);
        //step 3 切换了父级id之后需要获取新的level_str
        $newStr = $this->createLevelStr($id);
        if ($oldStr != $newStr) {
            //step 4 更新level_str
            $this->where('id', $id)->update(['level_str' => $newStr]);
            //step 5 修改所有子级的level_str
            $this->modifySonLevelStr($oldStr, $newStr, $id);
        }
    }

    /**
     * 获取所有父级
     * @param int $id
     */
    private function getParentsIds(int $id)
    {
        $level_str = $this->where('id', $id)->value('level_str');
        preg_match_all('/\d+/', $level_str, $m);

        if (count($m[0]) == 0) {
            return [];
        }
        $ids = array_reverse($m[0]);
        //移除最后一个元素
        array_pop($ids);
        if (!empty($ids)) {
            foreach ($ids as &$id) {
                $id = intval($id);
            }
        }
        return $ids;
    }

    /**
     * 获取层级ID 所有子级的数量，包括孙子 重孙子等
     * @param int $id
     * @return int
     */
    public function getSonCount(int $id): int
    {
        //SELECT count(id) from `level` where level_str like 'L1|%';
        //利用左索引，like可以走索引，否则GG
        //原理：level_str格式比如是L1|L2|L3| 所以可以用like统计数量
        $pStr = $this->getLevelStr($id);
        return $this->where([['level_str', 'like', $pStr . '%']])->where('is_delete', 0)->count('id') - 1;
    }

    /**
     * 获取层级ID紧邻的子级的数量，仅仅是下一子级，不包括孙子，重孙子等。
     * @param int $id
     * @return int
     */
    public function getNextSonCount(int $id): int
    {
        return $this->where([['pid', '=', $id], ['is_delete', '=', 0]])->count('id');
    }
}
