<?php
/*
 * H3C E352操作模块
 */
namespace Cli\Service;
use \Cli\Model\TelnetModel;
class Switch_7_Service extends SwitchBaseService{
    public function __construct(TelnetModel $switch){
        parent::__construct($switch);
        $this->version_id=7;
    }

    /**
     * 重启交换机
     */
    public function reboot(){
        $this->switch->command('reboot');
        sleep(3);
        $str='';
        $c=30;
        do{
            $str.=$this->switch->getBuffer();
        }while(strpos($str,'[Y/N]')===false&&$c-->0);
        if(strpos($str,'if you continue')!==false){
            $this->switch->command('Y');
            $str='';
            $c=30;
            do{
                $str.=$this->switch->getBuffer();
            }while(strpos($str,'[Y/N]')===false&&$c-->0);
        }
        if($c>0){
            $this->switch->command('Y');
            return ['code'=>1];
        }else{
            return ['code'=>2];
        }
    }

    /**
     * 获取端口概况
     * @return mixed
     */
    public function getBrief(){
        $data=$this->switch->exec('display brief interface');
        if(preg_match_all('/((GE|Eth)\d\/0\/\d{1,2})\s+(UP|DOWN).*?(trunk|access)/',$data,$result)){
            array_shift($result);
            array_splice($result,1,1);
            foreach($result[2] as &$val){
                if($val=='access')$val='A';
                else if($val=='trunk')$val='T';
            }
            F('Interface_'.$this->switch->getIp(),$result[0]);
            return ['no'=>1,'res'=>$result];
        }else{
            return ['no'=>2];
        }
    }
}