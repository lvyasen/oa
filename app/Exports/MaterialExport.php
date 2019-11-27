<?php

    namespace App\Exports;

    use App\Models\V1\Material;
    use Illuminate\Database\Eloquent\Collection;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;

    class MaterialExport implements FromCollection, ShouldAutoSize
    {
        private $data;

        /**
         * @return \Illuminate\Support\Collection
         */
        public function collection()
        {
            return new Collection($this->createData());
        }

        public function __construct($data)
        {
            $this->data = $data;
        }

        public function createData()
        {

            $status = [
                0 => '待审核',
                1 => '已审核',
                3 => '未通过',
            ];
            if (empty($this->data)) return false;
            $excelData   = [];
            $excelData[] = [
                '包装袋数量', '包装袋价格',
                '包装袋总费用', '拉链包装袋数量',
                '拉链单价', '拉链包装总价',
                '小票数量', '小票单价',
                '小票总价', '总费用',
                '购买时间', '审核状态',
            ];
            foreach ($this->data as $key => $val) {
                $excelData[] = [
                    $val['package_num'],
                    $val['package_price'],
                    $val['package_total_price'],
                    $val['zipper_package_num'],
                    $val['zipper_package_price'],
                    $val['zipper_package_total_price'],
                    $val['ticket_price'],
                    $val['ticket_num'],
                    $val['ticker_total_price'],
                    $val['total_price'],
                    date("Y/m/d H:i:s", $val['buy_time']),
                    $status[$val['status']],
                ];
            }
            return $excelData;

        }
    }
