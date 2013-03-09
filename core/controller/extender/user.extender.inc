<?php
/**
 * user extender for DocLister
 *
 * @category extender
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 09.03.2012
 * @version 1.0.0
 *
 * @TODO web_user
 * @param $userFields fields with user id
 */
 
class userDocLister extends extDocLister{
    private $fields=array();
    private $userinfo=array();

    protected function run(){
        if(''!=$this->_cfg['fields']){
            $this->_cfg['fields']=explode(",",$this->_cfg['fields']);
            $userid=array();
            foreach($this->_cfg['fields'] as $item){
               $this->fields[$item]=$this->DocLister->getOneField($item,true);
               $userid=array_merge($this->fields[$item],$userid);
            }
            $this->userinfo=$this->getInfo($userid);
            unset($userid);
        }
        return $this->userinfo;
    }
    public function getUser($field,$user){
        $out=array();
        if(isset($this->fields[$field],$this->userinfo[$user])){
            $out=$this->userinfo[$user];
        }
        return $out;
    }
    public function setUserData($arr){
        foreach($arr as $key=>$item){
            $out=$this->DocLister->renameKeyArr($this->getUser($key,$item[$key]),'user',$key);
            if(is_array($out) && count($out)>0){
                $arr=array_merge($arr,$out);
            }
        }
        return $arr;
    }
    private function getInfo($user=array()){
        $out=array();
        if(count($user)>0){
            $user=implode(",",$this->DocLister->cleanIDs($user));
            //without a.sessionid and u.password
          $sql=$this->modx->db->query("
                SELECT
                    u.id,
                    u.username,
                    a.fullname,
                    a.role,
                    a.email,
                    a.phone,
                    a.mobilephone,
                    a.blocked,
                    a.blockeduntil,
                    a.blockedafter,
                    a.logincount,
                    a.lastlogin,
                    a.thislogin,
                    a.failedlogincount,
                    a.dob,
                    a.gender,
                    a.country,
                    a.state,
                    a.zip,
                    a.fax,
                    a.photo,
                    a.comment
                FROM
                    {$this->modx->getFullTableName('manager_users')} as u
                LEFT JOIN
                    {$this->modx->getFullTableName('user_attributes')} as a
                ON
                    a.internalKey=u.id
                WHERE
                    u.id IN ({$user})
            ");
            while($tmp=$this->modx->db->getRow($sql)){
                $out[$tmp['id']]=$tmp;
            }
        }
        return $out;
    }
}