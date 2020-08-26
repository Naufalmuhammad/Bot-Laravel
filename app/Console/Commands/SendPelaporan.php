<?php

namespace App\Console\Commands;

use App\Keluhan;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use DB;

class SendPelaporan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Bot:Pelaporan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pelaporan Keluhan Pelanggan';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $keluhan = DB::table('tb_keluhan')
            ->join('tb_admin', 'tb_keluhan.id_user', '=', 'tb_admin.id_admin')
            ->select('tb_keluhan.tanggal_transaksi', 'tb_keluhan.nomor_tiket_gangguan','tb_keluhan.no_inet', 'tb_keluhan.nama_sistem', 'tb_keluhan.alamat_pelanggan', 'tb_keluhan.no_telepon', 'tb_keluhan.no_hp','tb_keluhan.no_hp2', 'tb_keluhan.keluhan_pelanggan', 'tb_keluhan.jenis_keluhan', 'tb_keluhan.solusi_csr', 'tb_keluhan.nomor_sc', 'tb_keluhan.catatan','tb_keluhan.open', 'tb_keluhan.close', 'tb_admin.id_telegram')
            ->get()->toArray();

        
        $start_date = \Carbon\Carbon::now();

        $waktu = array();
        $end_date = array();
        $newData = [];

        foreach ($keluhan as $key => $data) {
            $newData[] = array($data->tanggal_transaksi, $data->nomor_tiket_gangguan, $data->no_inet, $data->nama_sistem, $data->alamat_pelanggan, $data->no_telepon, $data->no_hp, $data->no_hp2, $data->keluhan_pelanggan, $data->jenis_keluhan, $data->solusi_csr, $data->nomor_sc, $data->catatan, $data->open, $data->close, $data->id_telegram);
        }

        foreach ($newData as $value) {            
            $end_date[] = \Carbon\Carbon::parse($value['0'])->format('Y-m-d');                    
        }        

        foreach ($end_date as $tanggal) {
            $waktu[] = $start_date->diffInDays($tanggal);
        }
        $test = array_merge($waktu, $newData);

        $dataa = [];
        foreach ($newData as $key => $test) {
            $dataa[] = [$test, $waktu[$key]];
        }
        
        foreach ($dataa as $w) {
            if ($w[1] >= 1 && $w[1] <= 3 && $w[0][13] == 1) {                
                $dt = 'Nomor Tiket Gangguan : ' . $w['0']['1'] . "\n";
                $dt .= 'Nomor Internet : ' . $w['0']['2']."\n";
                $dt .= 'Nama Di Sistem : ' . $w['0']['3']."\n";
                $dt .= 'Alamat Pelanggan : ' . $w['0']['4']."\n";
                $dt .= 'Nomor Telepon : ' . $w['0']['5']. "\n";
                $dt .= 'Nomor Hanphone : ' . $w['0']['6']."\n";
                $dt .= 'Nomor Hanphone 2 : ' . $w['0']['7']."\n";
                $dt .= 'Keluhan Pelanggan : ' . $w['0']['8']."\n";
                $dt .= 'Jenis Keluhan : ' . $w['0']['9']."\n";
                $dt .= 'Solusi CSR : ' . $w['0']['10']. "\n";
                $dt .= 'Nomor SC : ' . $w['0']['11']. "\n";
                $dt .= 'Catatan : ' . $w['0']['12']. "\n";
                if (!empty($w[0][15])) {
                    $response = Telegram::sendMessage([
                       'chat_id' => $w['0']['15'], 
                       'text' => "Peringatan, Segera Selesaikan!!!  \n ------------------------------------------------\n ----- Data Service Pelanggan -----\n ------------------------------------------------\n". $dt,
                       'parse_mode' => 'html'
                    ]);
                    $response = Telegram::sendMessage([
                       'chat_id' => '-1001367723905', 
                       'text' => "Peringatan, Segera Selesaikan!!!  \n ------------------------------------------------\n ----- Data Service Pelanggan -----\n ------------------------------------------------\n". $dt,
                       'parse_mode' => 'html'
                    ]);
                } else {}         
            }
            elseif ($w[1] >= 1 && $w[1] <= 3 && $w[0][14] == 1) {
            }
        }
    }
}