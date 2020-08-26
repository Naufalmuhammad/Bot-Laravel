<?php

namespace App\Http\Controllers;

use App\Keluhan;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;
use DB;

class OperasionalController extends Controller
{

    public function handle() {
        $config = [
        // Your driver-specific configuration
         "telegram" => [
            "token" => "1153495478:AAH7e5E9ykfq47dyE5ivdTawzT_PLHT7a9Q"
            ]
        ];

        // Load the driver(s) you want to use
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);

        // Create an instance
        $botman = BotManFactory::create($config);

        $botman->fallback(function (BotMan $bot) {
            $bot->types();
            $bot->reply('Maaf, Command yang Anda Masukan tidak Kami mengerti.');
        });

        // Give the bot something to listen for.
        $botman->hears('/start', function (BotMan $bot) {
            $bot->types();
            $result = $this->startMessage($bot->getUser()->getFirstName(), 
                        $bot->getUser()->getLastName(), 
                        $bot->getUser()->getUsername(),
                        $bot->getUser()->getId()
                );
            $bot->reply($result);
        });

        $botman->hears('/help', function (BotMan $bot) {
            $bot->types();
            $result = $this->help();
            $bot->reply($result);
        });

        $botman->hears('/laporansales', function (BotMan $bot) {
            $bot->types();
            $result = $this->monitorSales();
            $bot->reply($result);
        });

        // Start listening
        $botman->listen();
    } 

    public function startMessage($firstName, $lastName, $username, $id)
    {        
        $text = "Hello " . $firstName . " " . $lastName . "\n";
        $text .= "Username Anda adalah : @". $username . " dengan ID Anda " . $id . "\n\n";
        $text .= "Ini merupakan Bot yang akan membantu Anda dalam hal berikut: \n\n";        
        $text .= "1. Anda dapat mengetahui Laporan Sales dengan Command : /laporansales \n";
        $text .= "2. Untuk melihat menu bot bisa dengan Command : /help \n";        

        return $text;
    }
    public function help()
    {
        $text = "Ini merupakan Bot yang akan membantu Anda dalam hal berikut: \n\n";        
        $text = "1. Anda dapat mengetahui Laporan Sales dengan Command : /laporansales \n";

        return $text;
    }

    public function monitorSales()
    {
        // Create attachment
        // $images = public_path('https://dog.ceo/api/breeds/image/random');
        $images = 'satgasbumnjambi.com/gdoc1.png';
        $attachment = new Image($images, [
            'custom_payload' => true,
        ]);

        // dd($images);

        // Build message object
        $message = OutgoingMessage::create('Laporan Sales Datel Ma.Bungo')
                    ->withAttachment($attachment);
                    // dd($message);
        return $message;
    }

    public function alert()
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
        dd($dataa);
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

    public function storeData(  )
    {
        $telegram = new Api('1153495478:AAH7e5E9ykfq47dyE5ivdTawzT_PLHT7a9Q');
        $response = Telegram::sendMessage([
          'chat_id' => '984814377',
            'text' => 'Data Pelaporan Baru'
         ]);
    }
}
