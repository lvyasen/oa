<?php

    namespace App\Exports;

    use App\Models\V1\Material;
    use Illuminate\Database\Eloquent\Collection;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;

    class LogisticsExport implements FromCollection, ShouldAutoSize
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

            //        $status = [
            //            0 => '待审核',
            //            1 => '已审核',
            //            3 => '未通过',
            //        ];
            if (empty($this->data)) return false;
            $excelData   = [];
            $excelData[] = [
                '编号', '仓库单号',
                '物流跟踪号', '重量(kg)',
                '运输渠道', '运费',
                '处理费', '发货日期',
                '总费用',
            ];
            foreach ($this->data as $key => $val) {
                $excelData[] = [
                    $val['id'],
                    $val['warehouseOrderCode'],
                    $val['shippingMethodNo'],
                    $val['orderWeight'],
                    $val['shippingMethod'],
                    $val['shipFee'],
                    $val['platformFeeTotal'],
                    date('Y-m-d H:i:s',$val['dateWarehouseShipping']),
                    $val['totalFee'],
                ];
            }
            return $excelData;

        }
    }
