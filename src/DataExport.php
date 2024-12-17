<?php
/**
 * Copyright (C), 2024, Chongqing HeAo Network Technology Co., Ltd
 * FileName: DataExport.php
 * Description: 说明
 * @author KeHong
 * @Create Date 2024/12/16 9:48
 * @Update Date 2024/12/16 9:48 By 63402
 * @version v1.0
 */

namespace KeHongKing\ThinkphpCommon;

use \OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use think\exception\HttpException;

class DataExport
{
    public static function dataExport($title, $params, $sheet_name = '', $son_arr = [])
    {
        $keys_one = array_keys($title);
        $values_one = array_values($title);
        $writer = new Writer();
        $filePath = app()->getRootPath() . 'runtime/' . uniqid() . '.xlsx';
        $writer->openToFile($filePath);
        $rowFromValues = Row::fromValues($values_one);
        $writer->addRow($rowFromValues);
        $sheet = $writer->getCurrentSheet();
        if ($sheet_name) {
            $sheet->setName($sheet_name);
        }
        //验证数据
        $total_number = count($params);
        if (!empty($son_arr[0]['params'])) {
            $total_number = $total_number + count($son_arr[0]['params']);
        }
        if ($total_number > 50000) {
            throw new HttpException(400, '数据量不能超过50000', null, [], 400);
        }
        foreach ($params as $ks => $vs) {
            $cells = [];
            for ($is = 0; $is < count($keys_one); $is++) {
                $vals = null;
                $field_arr = explode('.', $keys_one[$is]);
                $field_arr_count = count($field_arr);
                if ($field_arr_count == 1 && isset($vs[$field_arr[0]])) {
                    $vals = $vs[$field_arr[0]];
                } elseif ($field_arr_count == 2 && isset($vs[$field_arr[0]][$field_arr[1]])) {
                    $vals = $vs[$field_arr[0]][$field_arr[1]];
                } elseif ($field_arr_count == 3 && isset($vs[$field_arr[0]][$field_arr[1]][$field_arr[2]])) {
                    $vals = $vs[$field_arr[0]][$field_arr[1]][$field_arr[2]];
                } elseif ($field_arr_count == 4 && isset($vs[$field_arr[0]][$field_arr[1]][$field_arr[2]][$field_arr[3]])) {
                    $vals = $vs[$field_arr[0]][$field_arr[1]][$field_arr[2]][$field_arr[3]];
                }
                $cells [] = Cell::fromValue($vals);
            }
            $multipleRows = [
                new Row($cells),
            ];
            $writer->addRows($multipleRows);
        }
        if (!empty($son_arr)) {
            //第二个表格
            $writer->addNewSheetAndMakeItCurrent();
            $sheet = $writer->getCurrentSheet();
            if (!empty($son_arr[0]['sheet_name'])) {
                $sheet->setName($son_arr[0]['sheet_name']);
            }
            $keys_two = array_keys($son_arr[0]['title']);
            $values_two = array_values($son_arr[0]['title']);
            $rowFromValues = Row::fromValues($values_two);
            $writer->addRow($rowFromValues);
            $paramss = $son_arr[0]['params'];
            foreach ($paramss as $kks => $vvs) {
                $cells = [];
                for ($iis = 0; $iis < count($keys_two); $iis++) {
                    $vals = null;
                    $field_arr = explode('.', $keys_two[$iis]);
                    $field_arr_count = count($field_arr);
                    if ($field_arr_count == 1 && isset($vvs[$field_arr[0]])) {
                        $vals = $vvs[$field_arr[0]];
                    } elseif ($field_arr_count == 2 && isset($vvs[$field_arr[0]][$field_arr[1]])) {
                        $vals = $vvs[$field_arr[0]][$field_arr[1]];
                    } elseif ($field_arr_count == 3 && isset($vvs[$field_arr[0]][$field_arr[1]][$field_arr[2]])) {
                        $vals = $vvs[$field_arr[0]][$field_arr[1]][$field_arr[2]];
                    } elseif ($field_arr_count == 4 && isset($vvs[$field_arr[0]][$field_arr[1]][$field_arr[2]][$field_arr[3]])) {
                        $vals = $vvs[$field_arr[0]][$field_arr[1]][$field_arr[2]][$field_arr[3]];
                    }
                    $cells [] = Cell::fromValue($vals);
                }
                $multipleRows = [
                    new Row($cells),
                ];
                $writer->addRows($multipleRows);
            }
        }
        $writer->close();
        $filename = date('YmdHis') . '.xlsx';//文件名称
        header('Content-Type:text/html;Charset=utf-8;');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Disposition:attachment;filename={$filename}");
        header("Content-Transfer-Encoding:binary");
        readfile($filePath);//下载
        unlink($filePath);//删除
        exit;
    }
}