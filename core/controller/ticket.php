<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Agel_Nash
 * Date: 20.12.12
 * Time: 20:12
 * To change this template use File | Settings | File Templates.
 */
 
class ticketDocLister extends DocLister{
    public function getUrl($id=0){
		/*
		* @TODO: ������ ����� ��������� �������������� $_GET ��������� ��� ������������� ��������� � ��������� � URL
		*/

        $link = ($this->extender['request'] instanceof requestDocLister) ? $this->extender['request']->getLink() : "";
		return $this->modx->makeUrl($this->modx->documentIdentifier, '', $link, 'full');
	}

    public function getDocs($tvlist=''){
        if($this->extender['paginate'] instanceof paginateDocLister){
            $pages=$this->extender['paginate']->init($this);
        }else{
            $this->setConfig(array('start'=>0));
        }

        $type = $this->getCFGDef('idType','parents');
        $this->_docs = ($type=='parents') ? $this->getChildrenList() : $this->getDocList();

        return $this->_docs;
	}

     public function getChildrenCount(){
        $sql=$this->modx->db->query("SELECT count(`id`) as `count` FROM ".$this->modx->getFullTableName("hesk_tickets"));
		return $this->modx->db->getValue($sql);
     }

    public function render($tpl=''){
		$out='';
		if($tpl==''){
			$tpl=$this->getCFGDef('tpl','');
		}

		if($tpl!=''){
			$locale=$this->getCFGDef('locale','');
			if($locale!=''){
				setlocale(LC_ALL, $locale);
			}
			$date=$this->getCFGDef('dateSource','createdon');

            $this->toPlaceholders(count($this->_docs),1,"display"); // [+display+] - ������� �������� �� ��������.

            $i=0;
            $sysPlh=$this->renameKeyArr($this->_plh,$this->getCFGDef("sysKey","dl"));

			foreach($this->_docs as $item){
				if($this->extender['summary'] instanceof summaryDocLister){
                    if(mb_strlen($item['introtext'], 'UTF-8') > 0){
                        $item['summary']=$item['introtext'];
                    }else{
                       $item['summary']= $this->extender['summary']->init($this,array("content"=>$item['content'],"summary"=>$this->getCFGDef("summary","")));
                    }
				}

                $item=array_merge($item,$sysPlh); //������ ����� �������� ��� ������������ ������������� ����� $modx->toPlaceholders � ��������� ��� � ditto, ��� � � ��������� sysKey
                $item['iteration']=$i; //[+iteration+] ���������� ����� �������� �� ����.
                $item['url'] = ''; //@TODO: [+url+] - ������ �� ��������� ��������.
                $item['author'] = ''; //@TODO: [+author+] � ��� ������. �������� �� createdby ��� editedby

                //@TODO: [+active+] ���� �������� � ������� ���������, �� $item['id'] ����� � ���������� �� $modx->documentIdentifier
                $item['active'] = ($this->modx->documentIdentifier == $item['id']) ? 'active' : '';  //[+active+] - 0 ��� 1 ���� $modx->documentIdentifer ��������� � ID �������� ��������
                $item['date']=(isset($item[$date]) && $date!='createdon' && $item[$date]!=0 && $item[$date]==(int)$item[$date]) ? $item[$date] : $item['createdon'];
				$item['date']=strftime($this->getCFGDef('dateFormat','%d.%b.%y %H:%M'),$item['date']+$this->modx->config['server_offset_time']);
                $tmp=$this->modx->parseChunk($tpl,$item,"[+","+]");
                if($this->getCFGDef('contentPlaceholder',0)!==0){
                    $this->toPlaceholders($tmp,1,"item[".$i."]"); // [+item[x]+] � �������������� ����� ��������������� ���������
                }
                $out.=$tmp;
                $i++;
			}
		}else{
			$out='none TPL';
		}

		/*
		* @TODO: [+filter+] - ������ ���������� (��� ����������� � URL)
		*/

		return $this->toPlaceholders($out);
	}


    protected  function getChildrenList(){
		/*
		* @TODO: 3) ������������ ����� � ��������� ������� (���� ��������� ��������� � ���� ��������������� ������)
		* @TODO: 5) �������� ���������� �� �������� ���������� ���������
		*/
		$sql=$this->modx->db->query("SELECT * FROM ".$this->modx->getFullTableName("hesk_tickets")." ORDER BY ".$this->getCFGDef('sortBy','createdon')." ".$this->getCFGDef('order','DESC')." ".$this->LimitSQL($this->getCFGDef('queryLimit',0)));
		$sql=$this->modx->db->makeArray($sql);
		$out=array();
		foreach($sql as $item){
			$out[$item['id']]=$item;
		}
		return $out;
	}
    
}