<?php

/**
 * Class DLpaginate
 */
class DLpaginateReversed extends DLpaginate
{
    /**
     * @param $tpl
     * @param $num
     * @return mixed
     */
    protected function renderItemTPL($tpl, $num)
    {
        $_num = $this->total_pages + 1 - $num;
        return str_replace(array('[+num+]', '[+link+]'), array($_num, $this->get_pagenum_link($_num)), $tpl);
    }

    /**
     * @param $id
     * @return mixed|string
     */
    public function get_pagenum_link($id)
    {
        $flag = (strpos($this->target, '?') === false);
        $value = $this->getPageQuery($id);
        if ($flag && !empty($this->urlF)) {
            $out = str_replace($this->urlF, $value, $this->target);
        } else {
            $out = $this->target;
            if ($id > 0 && $id < $this->total_pages) {
                $out .= ($flag ? "?" : "&") . $this->parameterName . "=" . $value;
            }
        }

        return $out;
    }
}
