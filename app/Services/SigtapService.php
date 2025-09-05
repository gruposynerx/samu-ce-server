<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SigtapService
{
    /**
     * Import a SIGTAP file.
     */
    public static function import(UploadedFile $sigtapZip)
    {
        if ($sigtapZip->extension() != 'zip') {
            return redirect()->route('sigtap.index')->with('erro', 'Arquivo inválido!');
        }

        $salvarzip = $sigtapZip->storeAS('tempzip', 'arquivo.zip');

        $arquivozip = $sigtapZip;
        $destinationPath = public_path('/tempzip');

        $name = 'sigtap.zip';
        //----------------------descompactando arquivo Zip----------------------------------
        $arquivozip->move($destinationPath, $name);
        $path = public_path('tempzip/' . $name);
        \Madnest\Madzipper\Facades\Madzipper::make($path)->extractTo('temptxt');

        if (file_exists($path)) {
            unlink($path);
        }

        //---------------------- Tabela ProcedimentoxServico ----------------------------------
        //        DB::table('procedure_services')->delete();
        $collection = collect();
        $file = public_path('temptxt/rl_procedimento_servico.txt');
        $openedFile = file($file);

        foreach ($openedFile as $line) {
            $procedureCode = trim(substr($line, 0, 10));
            $serviceCode = trim(substr($line, 10, 3));
            $classificationCode = trim(substr($line, 13, 3));
            $collection->push([
                'procedure_code' => $procedureCode,
                'service_code' => $serviceCode,
                'classification_code' => $classificationCode,
            ]);
        }

        DB::table('procedure_services')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de CBO----------------------------------
        //        DB::table('occupations')->delete();
        $file = public_path('temptxt/tb_ocupacao.txt');
        $openedFile = file($file);
        $collection = collect();

        foreach ($openedFile as $line) {
            $occupationCode = trim(substr($line, 0, 6));
            $occupationName = utf8_encode(trim(substr($line, 6, 150)));

            $collection->push([
                'code' => $occupationCode,
                'name' => $occupationName,
                'active' => true,
            ]);
        }

        DB::table('occupations')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de CID----------------------------------
        //        DB::table('icds')->delete();
        $file = public_path('temptxt/tb_cid.txt');
        $openedFile = file($file);
        $collection = collect();

        foreach ($openedFile as $line) {
            $code = trim(substr($line, 0, 4));
            $description = utf8_encode(trim(substr($line, 4, 100)));
            $permittedGender = trim(substr($line, 105, 1));

            $collection->push([
                'code' => $code,
                'description' => $description,
                'permitted_gender' => $permittedGender,
                'active' => true,
            ]);
        }

        DB::table('icds')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de PROCEDIMENTO----------------------------------
        //        DB::table('tbb_procedimento')->delete();
        $file = public_path('temptxt/tb_procedimento.txt');
        $openedFile = file($file);
        $collection = collect();

        foreach ($openedFile as $line) {
            $code = trim(substr($line, 0, 10));
            $code9 = trim(substr($line, 0, 9));
            $procedureName = utf8_encode(trim(substr($line, 10, 250)));
            $complexityName = trim(substr($line, 260, 1));
            $permittedGender = trim(substr($line, 261, 1));
            $maxPatient = trim(substr($line, 262, 4));
            $minAge = trim(substr($line, 274, 4));
            $maxAge = trim(substr($line, 278, 4));
            $unitValue = trim(substr($line, 292, 8)) . '.' . trim(substr($line, 300, 2));
            $financingCode = trim(substr($line, 312, 2));
            $rubric = trim(substr($line, 314, 6));

            $collection->push([
                'code' => $code,
                'code_9' => $code9,
                'name' => $procedureName,
                'complexity_type' => $complexityName,
                'permitted_gender' => $permittedGender,
                'max_per_patient' => $maxPatient,
                'min_age' => $minAge,
                'max_age' => $maxAge,
                'unit_value' => $unitValue,
                'financing_code' => $financingCode,
                'rubric' => $rubric,
                'active' => 1,
            ]);
        }

        DB::table('procedures')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de Grupo----------------------------------
        //        DB::table('groups')->delete();
        $file = public_path('temptxt/tb_grupo.txt');
        $openedFile = file($file);
        $collection = collect();

        foreach ($openedFile as $line) {
            $code = trim(substr($line, 0, 2));
            $name = utf8_encode(trim(substr($line, 2, 100)));

            $collection->push([
                'code' => $code,
                'name' => $name,
                'active' => true,
            ]);
        }

        DB::table('groups')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de SUBGRUPO----------------------------------
        //        DB::table('sub_group')->delete();
        $file = public_path('temptxt/tb_sub_grupo.txt');
        $openedFile = file($file);
        $collection = collect();

        foreach ($openedFile as $line) {
            $groupCode = trim(substr($line, 0, 2));
            $subGroupCode = trim(substr($line, 2, 2));
            $subGroupName = utf8_encode(trim(substr($line, 4, 100)));

            $collection->push([
                'group_code' => $groupCode,
                'sub_group_code' => $subGroupCode,
                'sub_group_name' => $subGroupName,
                'active' => 1,
            ]);
        }

        DB::table('sub_groups')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de tipo ProcediemntoxCBO----------------------------------
        //        DB::table('procedure_occupations')->delete();
        $file = public_path('temptxt/rl_procedimento_ocupacao.txt');
        $openedFile = file($file);
        $collection = collect();

        foreach ($openedFile as $line) {
            $procedureCode = trim(substr($line, 0, 10));
            $occupationCode = trim(substr($line, 10, 6));

            $collection->push([
                'procedure_code' => $procedureCode,
                'occupation_code' => $occupationCode,
            ]);

            if ($collection->count() > 20000) {
                DB::table('procedure_occupations')->insertOrIgnore($collection->toArray());

                $collection = collect();
            }
        }

        DB::table('procedure_occupations')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de tipo ProcediemntoxCid----------------------------------

        // DB::table('procedure_icds')->delete();
        $file = public_path('temptxt/rl_procedimento_cid.txt');
        $openedFile = file($file);
        $collection = collect();

        foreach ($openedFile as $line) {
            $procedureCode = trim(substr($line, 0, 10));
            $icdCode = trim(substr($line, 10, 4));

            $collection->push([
                'procedure_code' => $procedureCode,
                'icd_code' => $icdCode,
            ]);

            if ($collection->count() > 20000) {
                DB::table('procedure_icds')->insertOrIgnore($collection->toArray());

                $collection = collect();
            }
        }

        DB::table('procedure_icds')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de DESCRICAO PROCEDIMENTO----------------------------------

        DB::table('procedure_descriptions')->delete();
        $file = public_path('temptxt/tb_descricao.txt');
        $openedFile = file($file);
        $collection = collect();

        foreach ($openedFile as $line) {
            $procedureCode = trim(substr($line, 0, 10));
            $procedureDescription = utf8_encode(trim(substr($line, 10, 4000)));

            $collection->push([
                'procedure_code' => $procedureCode,
                'description' => $procedureDescription,
            ]);

            if ($collection->count() > 20000) {
                DB::table('procedure_descriptions')->insertOrIgnore($collection->toArray());

                $collection = collect();
            }
        }

        DB::table('procedure_descriptions')->insertOrIgnore($collection->toArray());

        //----------------------Atualizando Tabela de tipo ProcediemntoxDetalhe----------------------------------
        //        DB::table('rl_procedimento_detalhe')->delete();
        //        $file = public_path('temptxt/rl_procedimento_detalhe.txt');
        //        $openedFile = file($file);
        //
        //        foreach ($openedFile as $line) {
        //            $codigoprocedimento = trim(substr($line, 0, 10));
        //            $codigodetalhe = trim(substr($line, 10, 3));
        //
        //            $dadosexistente02 = DB::table('rl_procedimento_detalhe')
        //                ->where([['codigoprocedimento', $codigoprocedimento], ['codigodetalhe', $codigodetalhe]])->get();
        //
        //            if (!isset($dadosexistente02['codigoprocedimento'])) {
        //                DB::table('rl_procedimento_detalhe')->insert([
        //                    'codigoprocedimento' => $codigoprocedimento,
        //                    'codigodetalhe' => $codigodetalhe,
        //
        //                ]);
        //            }
        //        }
        //

        //        //----------------------Atualizando Tabela de SERVICO----------------------------------
        //        DB::table('tbb_servico')->delete();
        //        $file = public_path('temptxt/tb_servico.txt');
        //        $openedFile = file($file);
        //
        //        foreach ($openedFile as $line) {
        //            $codigoservico = trim(substr($line, 0, 3));
        //            $nomeservico = utf8_encode(trim(substr($line, 3, 120)));
        //
        //            DB::table('tbb_servico')->insert([
        //                'codigoservico' => $codigoservico,
        //                'nomeservico' => $nomeservico,
        //                'ativo' => 1,
        //            ]);
        //        }

        //        //----------------------Atualizando Tabela de SERVICO CLASSIFICACAO----------------------------------
        //        DB::table('tbb_servico_classificacao')->delete();
        //        $file = public_path('temptxt/tb_servico_classificacao.txt');
        //        $openedFile = file($file);
        //
        //        foreach ($openedFile as $line) {
        //            $codigoservico = trim(substr($line, 0, 3));
        //            $codigoclassificacao = trim(substr($line, 3, 3));
        //            $nomeclassificacao = utf8_encode(trim(substr($line, 6, 150)));
        //
        //            DB::table('tbb_servico_classificacao')->insert([
        //                'codigoservico' => $codigoservico,
        //                'codigoclassificacao' => $codigoclassificacao,
        //                'nomeclassificacao' => $nomeclassificacao,
        //                'ativo' => 1,
        //            ]);
        //        }

        //----------------------Atualizando Tabela de REGISTRO----------------------------------
        //
        //        DB::table('tbb_registro')->delete();
        //        $file = public_path('temptxt/tb_registro.txt');
        //        $openedFile = file($file);
        //
        //        foreach ($openedFile as $line) {
        //            $codigotiporegistro = trim(substr($line, 0, 2));
        //            $nometiporegistro = utf8_encode(trim(substr($line, 2, 50)));
        //
        //            DB::table('tbb_registro')->insert([
        //                'codigotiporegistro' => $codigotiporegistro,
        //                'nometiporegistro' => $nometiporegistro,
        //                'ativo' => 1,
        //            ]);
        //        }

        //
        //        //----------------------Atualizando Tabela de tipo ProcediemntoxRegistro----------------------------------
        //        DB::table('rl_procedimento_registro')->delete();
        //        $file = public_path('temptxt/rl_procedimento_registro.txt');
        //        $openedFile = file($file);
        //
        //        foreach ($openedFile as $line) {
        //            $codigoprocedimento = trim(substr($line, 0, 10));
        //            $codigotiporegistro = trim(substr($line, 10, 2));
        //
        //            DB::table('rl_procedimento_registro')->insert([
        //                'codigoprocedimento' => $codigoprocedimento,
        //                'codigotiporegistro' => $codigotiporegistro,
        //            ]);
        //        }

        //        //----------------------Atualizando Tabela de SERVICO CLASSIFICACAO----------------------------------
        //        DB::table('tbb_servico_classificacao')->delete();
        //        $file = public_path('temptxt/tb_servico_classificacao.txt');
        //        $openedFile = file($file);
        //
        //        foreach ($openedFile as $line) {
        //            $codigoservico = trim(substr($line, 0, 3));
        //            $codigoclassificacao = trim(substr($line, 3, 3));
        //            $nomeclassificacao = utf8_encode(trim(substr($line, 6, 150)));
        //
        //            DB::table('tbb_servico_classificacao')->insert([
        //                'codigoservico' => $codigoservico,
        //                'codigoclassificacao' => $codigoclassificacao,
        //                'nomeclassificacao' => $nomeclassificacao,
        //                'ativo' => 1,
        //
        //            ]);
        //        }
    }
}
