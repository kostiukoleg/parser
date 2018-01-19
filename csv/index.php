<?php

/**
 * ����� ��� ������ � csv-������� 
 * @author ������ ������ ox2.ru  
 */
class CSV {

    private $_csv_file = null;

    /**
     * @param string $csv_file  - ���� �� csv-�����
     */
    public function __construct($csv_file) {
        if (file_exists($csv_file)) { //���� ���� ����������
            $this->_csv_file = $csv_file; //���������� ���� � ����� � ����������
        }
        else throw new Exception("���� \"$csv_file\" �� ������"); //���� ���� �� ������ �� �������� ����������
    }

    public function setCSV(Array $csv) {
        $handle = fopen($this->_csv_file, "a"); //��������� csv ��� ��-������, ���� ������� w, ��  ��������� ������� ���� � csv ����� �������

        foreach ($csv as $value) { //�������� ������
            fputcsv($handle, explode("~", $value), ";"); //����������, 3-�� �������� - ����������� ����
        }
        fclose($handle); //���������
    }

    /**
     * ����� ��� ������ �� csv-�����. ���������� ������ � ������� �� csv
     * @return array;
     */
    public function getCSV() {
        $handle = fopen($this->_csv_file, "r"); //��������� csv ��� ������

        $array_line_full = array(); //������ ����� ������� ������ �� csv
        while (($line = fgetcsv($handle, 0, ";")) !== FALSE) { //�������� ���� csv-����, � ������ ���������. 3-�� �������� ����������� ����
            $array_line_full[] = $line; //���������� ������� � ������
        }
        fclose($handle); //��������� ����
        return $array_line_full; //���������� ���������� ������
    }

}

//try {
    //$csv = new CSV("ox2.csv"); //��������� ��� csv
    /**
     * ������ �� CSV  (� ����� �� �����)
     */
    //echo "<h2>CSV �� ������:</h2>";
    //$get_csv = $csv->getCSV();
    //foreach ($get_csv as $value) { //�������� �� �������
        //echo "���: " . $value[0] . "<br/>";
        //echo "���������: " . $value[1] . "<br/>";
        //echo "�������: " . $value[2] . "<br/>";
        //echo "--------<br/>";
   // }

    /**
     * ������ ����� ���������� � CSV
     */
   // $arr = array("������� �.�.;����� OX2.ru;89031233333",
        //"�������� �.�.;���� OX2.ru;89162233333");
    //$csv->setCSV($arr);
//}
//catch (Exception $e) { //���� csv ���� �� ����������, ������� ���������
    //echo "������: " . $e->getMessage();
//}
?>