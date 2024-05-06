<?PHP
            /**
             * case 'simulationStart': の時に一度だけ読み込まれるファイルです
             * （必要な値はセッションに保持する必要があります）
             *
             */


            // == シミュレーションの最初にadmin系整備のために助走する日数
            //
            // 遡る：昔recent(adj.), 今retroactive(adj.)
            //
            const BS_REREOACTIVE_DAYS = 12;


            /**
             * シミュレーションパラメータ
             * （使用機会の減少から廃止見込み）
             *
             */
            $simulationParam->AYAYA = 35;
            $simulationParam->noriP = 51;

            $simulationParam->SIMULATOR_RESPONSE_SECOND = 30;


?>
