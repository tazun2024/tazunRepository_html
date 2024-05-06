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

            // １か月シミュレーションの助走用さかのぼり日数
            // ロリポップでのシミュレーションバッチは月替わりで助走のし直しを不要とした連続シミュレーション
            //

            // 2017/01事象を受けてさくらに合わせる
          //const FS_REREOACTIVE_DAYS = 5;
            const FS_REREOACTIVE_DAYS = 50;


            /**
             * シミュレーションパラメータ
             * （使用機会の減少から廃止見込み）
             *
             */
            $simulationParam->AYAYA = 35;
            $simulationParam->noriP = 51;

            /**
             * どのくらい連続してシミュレーションループを行うか（ブラウザーresponseタイムアウトとの調整）
             */
            $simulationParam->SIMULATOR_RESPONSE_SECOND = 10; // 30秒

            /**
             * 通貨type
             *
             */
            $simulationParam->SymbolType = $FS_PARAM['FS_EXECUTE_SYMBOLTYPE'];
?>
