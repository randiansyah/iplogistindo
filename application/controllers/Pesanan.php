<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'core/Admin_Controller.php';
class Pesanan extends Admin_Controller {
  public function __construct()
  {
    parent::__construct();
    $this->load->model('customer_model'); 
    $this->load->model('transaksi_model');
    $this->load->model('transaksi_ukuran_model');
    $this->load->model('wilayah_model');
  }
  public function index()
  {
    $this->load->helper('url');
    if($this->data['is_can_read']){
      $this->data['content'] = 'admin/order/list_v';   
    }else{
      $this->data['content'] = 'errors/html/restrict'; 
    }
    
    $this->load->view('admin/layouts/page',$this->data);  
  }

    public function dataList()
  {
    $columns = array( 
            0 => 'id', 
            1 => 'id_transaksi',
            2 => 'nama',
            3 => 'telp',
            4 => 'kirim_via',
            5 => 'asal',
            6 => 'tujuan',
        );

      $order = $columns[$this->input->post('order')[0]['column']];
      $dir = $this->input->post('order')[0]['dir'];
      $search = array();
      $where = array('tipe' => 'order');
      if (!$this->data['is_superadmin']) {
        $where['created_by'] = $this->data['users']->id; 
      }
      $limit = 0;
      $start = 0;
      $totalData = $this->transaksi_model->getCountAllBy($limit,$start,$search,$order,$dir,$where);       

        $searchColumn = $this->input->post('columns');
        $isSearchColumn = false;
        

      if($isSearchColumn){
        $totalFiltered = $this->transaksi_model->getCountAllBy($limit,$start,$search,$order,$dir,$where); 
      }else{
        $totalFiltered = $totalData;
      }  
       
    $limit = $this->input->post('length');
    $start = $this->input->post('start');
    $datas = $this->transaksi_model->getAllBy($limit,$start,$search,$order,$dir,$where);
     
        $new_data = array();
        if(!empty($datas))
        {
            foreach ($datas as $key=>$data)
            {   

              $id_transaksi = "<a href='".base_url()."Pesanan/detail/".$data->id_transaksi."'><i class='fa fa-search'></i> ".$data->id_transaksi."</a>";
          
             $posting="";
           
             $posting = "<a href='".base_url()."Pesanan/edit/".$data->id_transaksi."'><i class='fa fa-check'></i> Posting</a>&nbsp;&nbsp;&nbsp;<a href='#' 
                  url='".base_url()."pesanan/destroy/".$data->id_transaksi."'
                  class='delete' 
                   ><i class='fa fa-trash'></i>&nbsp;Hapus
                  </a>";  

            if($this->data['is_superadmin']){
              $adminAct = "<a href='#' 
                  url='".base_url()."Pesanan/destroy/".$data->id_transaksi."'
                  class='delete' 
                   ><i class='fa fa-trash'></i>&nbsp;Hapus
                  </a>";
               }else{
        $adminAct = "";
               }
              

              if($data->posting >= 1){
                $posting = '<i class="fa fa-check-circle"></i> Posted';
              }if($data->posting == 4){
                $posting = '<i class="fa fa-thumbs-up"></i> Selesai';
              }

              $status = '<i class="fa fa-warning"></i> Belum Diposting';

              if($data->status == 1){
                $status = '<i class="fa fa-spinner"></i> Menunggu<br>'.$adminAct;
              }if($data->status == 2){
                $status = '<i class="fa fa-spinner"></i> Sedang Diproses<br>'.$adminAct;
              }if($data->status == 3){
                $status = '<i class="fa fa-truck"></i> Sedang Di Pickup<br>'.$adminAct;
              }if($data->status == 4){
                $status = '<i class="fa fa-truck"></i> Sedang Di Pickup<br>'.$adminAct;
              }
  
              $id_transaksi = "<a href='".base_url()."Pesanan/detail/".$data->id_transaksi."'><i class='fa fa-search'></i> ".$data->id_transaksi."</a>"; 
           
            $nestedData['id']   = $start+$key+1;
            $nestedData['nama'] = $data->nama;
            $nestedData['id_transaksi']  = $id_transaksi;
            $nestedData['telp']  = $data->telp;
            $nestedData['kirim_via']  = 'Jalur '.$data->kirim_via;
            $nestedData['asal']  = $data->asal;
            $nestedData['tujuan']  = $data->tujuan;
            $nestedData['posting']  = $posting;
            $nestedData['status']  = $status;
            
         
            $new_data[] = $nestedData; 
        }
    }
      
    $json_data = array(
                "draw"            => intval($this->input->post('draw')),  
                "recordsTotal"    => intval($totalData),  
                "recordsFiltered" => intval($totalFiltered), 
                "data"            => $new_data   
                );
        
    echo json_encode($json_data); 
  }

public function create(){
$this->form_validation->set_rules('id_transaksi',"transaksi tidak boleh kosong", 'trim|required'); 
$this->form_validation->set_rules('pelanggan',"pelanggan tidak boleh kosong", 'trim|required'); 
   $date = date('y-m-d H:i:s');
   $kode = $this->transaksi_model->getKode();

   $id_pelanggan = $this->input->post('pelanggan');

   $pelanggan = $this->customer_model->getAllById(array('id' =>$id_pelanggan));


  if($this->form_validation->run() === TRUE){

      $harga_barang1 = str_replace(".","",$this->input->post('harga_barang'));
      $harga_barang  = str_replace("Rp ","",$harga_barang1);
      $KDPROP  = $this->input->post('kdprop');
      $KDKAB   = $this->input->post('kdkab');
      $KDPROP_tujuan  = $this->input->post('kdprop_tujuan');
      $KDKAB_tujuan   = $this->input->post('kdkab_tujuan');
      $wilayah_asal = $this->wilayah_model->getOneBy(array('KDPROP' => $KDPROP, 'KDKAB' => $KDKAB));
      $wilayah_tujuan = $this->wilayah_model->getOneBy(array('KDPROP' => $KDPROP_tujuan, 'KDKAB' => $KDKAB_tujuan));
  
    $data = array (
    'id_transaksi' => $this->input->post('id_transaksi'),
    'kode_pelanggan' => $this->input->post('pelanggan'),
    'nama' => (!empty($pelanggan))?$pelanggan[0]->nama:"",
    'telp' => (!empty($pelanggan))?$pelanggan[0]->telp:"",
    'kirim_via' => $this->input->post('kirim_via'),
    'jenis_pengiriman' => $this->input->post('jenis_pengiriman'),
    'jenis_pembayaran' => $this->input->post('jenis_pembayaran'),
    'asal' => $wilayah_asal->NMKAB,
    'KDPROP_asal' => $wilayah_asal->KDPROP,
    'KDKAB_asal' => $wilayah_asal->KDKAB,
    'tujuan' => $wilayah_tujuan->NMKAB,
    'KDPROP_tujuan' => $wilayah_tujuan->KDPROP,
    'KDKAB_tujuan' => $wilayah_tujuan->KDKAB,
    'alamat' => $this->input->post('alamat'),
    'catatan' => $this->input->post('catatan'),
    'alamat_tujuan' => $this->input->post('alamat_tujuan'),
    'tipe' => 'order',
    'posting' => 0,
    'status' => 0,
    'created_by' => $this->input->post('user_input'),
    'created_at' => $this->input->post('waktu_input')
     );  
   
   
   
    $insert_pesanan = $this->transaksi_model->insert($data);
    if ($insert_pesanan)
      { 
        $no = $this->input->post('no');
        $jenis_paket = $this->input->post('jenis_paket');
        $nama_barang = $this->input->post('nama_barang');
        $harga_barang = $this->input->post('harga_barang');
        $jenis_barang = $this->input->post('jenis_barang');
        $berat_barang = $this->input->post('berat');
        $jumlah_coli = $this->input->post('total_coli');
        $berat_total = $this->input->post('berat_total');
        $panjang_barang = $this->input->post('panjang');
        $lebar_barang = $this->input->post('lebar');
        $tinggi_barang = $this->input->post('tinggi');
        $packing = $this->input->post('packing');
        $asuransi = $this->input->post('asuransi');
       
        $order_detail = [];
      
        foreach ($no as $key => $val) {
          if($no[$key] > 0 ){
          $order_detail[] = array(
            'id_transaksi'  => $this->input->post('id_transaksi'),
            'harga_barang'   => $harga_barang[$key],
            'jenis_barang'   => $jenis_barang[$key],
            'berat'   => $berat_barang[$key],
            'panjang'   => $panjang_barang[$key],
            'lebar'   => $lebar_barang[$key],
            'tinggi'   => $tinggi_barang[$key],
            'packing'   => $packing[$key],
            'asuransi'   => $asuransi[$key],
            'jumlah_coli'   => $jumlah_coli[$key],
            'berat_total'   => $berat_total[$key],

          );
          
          }
        }
               


        $this->db->insert_batch('transaksi_ukuran', $order_detail);

        $this->session->set_flashdata('message', "Pesanan Baru Berhasil Disimpan");
        redirect("Pesanan");
      }else{
        $this->session->set_flashdata('message_error',"Pesanan Baru Gagal Disimpan");
        redirect("Pesanan");
      }     
                

  }else{
       
    $this->data['content'] = 'admin/order/create_v';
    $this->data['waktu_input'] = $date;
    $this->data['pelanggan'] = $this->customer_model->getAllById();
    $this->data['kode'] = 'LGS'.$kode;
    $this->data['data_provinsi'] = $this->wilayah_model->getAllProvince();  

    $this->load->view('admin/layouts/page',$this->data);
  }
 }

 public function edit($id)
  { 
    $this->form_validation->set_rules('id_transaksi',"transaksi tidak boleh kosong", 'trim|required'); 
$this->form_validation->set_rules('pelanggan',"pelanggan tidak boleh kosong", 'trim|required'); 
   $date = date('y-m-d H:i:s');
   $kode = $this->transaksi_model->getKode();

   $id_pelanggan = $this->input->post('pelanggan');

   $pelanggan = $this->customer_model->getAllById(array('id' =>$id_pelanggan));

    if ($this->form_validation->run() === TRUE)
    { 
      $data = array(  
        'posting'      => 1,
        'status'       => 1,
      );

      $update = $this->transaksi_model->update($data,array("id_transaksi"=>$id)); 
      
      if ($update)
      {
        

        $this->session->set_flashdata('message', "Pesanan Baru Berhasil di posting");
        redirect("Pesanan");
      }else{
        $this->session->set_flashdata('message_error', "Pesanan Baru Gagal Diposting");
        redirect("Pesanan","refresh");
      }
    }else{
      if(!empty($_POST)){ 
        $this->session->set_flashdata('message_error',validation_errors());
        return redirect("Pesanan/edit/".$id);  
      }else{

        $data = $this->transaksi_model->getAllById(array("id_transaksi"=>$id));
        $barang = $this->transaksi_ukuran_model->getAllById(array("id_transaksi"=>$id));


        $this->data['id_transaksi'] =   (!empty($data))?$data[0]->id_transaksi:"";
        $this->data['kode_pelanggan'] =   (!empty($data))?$data[0]->kode_pelanggan:"";
        $this->data['kirim_via'] =   (!empty($data))?$data[0]->kirim_via:"";
        $this->data['catatan'] =   (!empty($data))?$data[0]->catatan:"";
        $this->data['jenis_pengiriman'] =   (!empty($data))?$data[0]->jenis_pengiriman:"";
        $this->data['jenis_pembayaran'] =   (!empty($data))?$data[0]->jenis_pembayaran:"";
         $this->data['asal'] =   (!empty($data))?$data[0]->asal:"";
        $this->data['KDPROP_asal'] =   (!empty($data))?$data[0]->KDPROP_asal:"";
        $this->data['KDKAB_asal'] =   (!empty($data))?$data[0]->KDKAB_asal:"";
        $this->data['KDPROP_tujuan'] =   (!empty($data))?$data[0]->KDPROP_tujuan:"";
        $this->data['KDKAB_tujuan'] =   (!empty($data))?$data[0]->KDKAB_tujuan:"";
        $this->data['tipe_berat'] =   (!empty($data))?$data[0]->tipe_berat:"";
     
        $this->data['alamat'] =   (!empty($data))?$data[0]->alamat:"";
        $this->data['alamat_tujuan'] =   (!empty($data))?$data[0]->alamat_tujuan:"";
        $this->data['status']     =   (!empty($data))?$data[0]->status:""; 
        $this->data['posting']    =   (!empty($data))?$data[0]->posting:"";  
        $this->data['waktu_input']    =   (!empty($data))?$data[0]->created_at:"";
        $this->data['packing']    =   (!empty($data))?$data[0]->packing:"";
        $this->data['asuransi']    =   (!empty($data))?$data[0]->asuransi:"";
        $this->data['barang']    =   $barang;
        
        $this->data['content'] = 'admin/order/edit_v'; 
        $this->data['pelanggan'] = $this->customer_model->getAllById();
        $this->data['data_provinsi'] = $this->wilayah_model->getAllProvince();  
       
        $this->load->view('admin/layouts/page',$this->data); 
      }  
    }   
  } 

   public function detail($id)
  { 
    $this->form_validation->set_rules('id_transaksi',"transaksi tidak boleh kosong", 'trim|required'); 
$this->form_validation->set_rules('pelanggan',"pelanggan tidak boleh kosong", 'trim|required'); 
   $date = date('y-m-d H:i:s');
   $kode = $this->transaksi_model->getKode();

   $id_pelanggan = $this->input->post('pelanggan');

   $pelanggan = $this->customer_model->getAllById(array('id' =>$id_pelanggan));

    if ($this->form_validation->run() === TRUE)
    { 
      $data = array(  
        'posting'      => 1,
        'status'       => 1,
      );

      $update = $this->transaksi_model->update($data,array("id_transaksi"=>$id)); 
      
      if ($update)
      {
        $this->transaksi_ukuran_model->delete(array("id_transaksi"=>$id));
        
        $ukuran = array(
        'id_transaksi' => $this->input->post('id_transaksi'),
        'panjang' => $this->input->post('panjang'),
        'lebar' => $this->input->post('lebar'),
        'tinggi' => $this->input->post('tinggi'),

       );

        $insert_ukuran = $this->transaksi_ukuran_model->insert($ukuran);

        $this->session->set_flashdata('message', "Pesanan Baru Berhasil di posting");
        redirect("Pesanan");
      }else{
        $this->session->set_flashdata('message_error', "Pesanan Baru Gagal Diposting");
        redirect("Pesanan","refresh");
      }
    }else{
      if(!empty($_POST)){ 
        $this->session->set_flashdata('message_error',validation_errors());
        return redirect("Pesanan/edit/".$id);  
      }else{

        $data = $this->transaksi_model->getAllById(array("id_transaksi"=>$id));
        $barang = $this->transaksi_ukuran_model->getAllById(array("id_transaksi"=>$id));


        $this->data['id_transaksi'] =   (!empty($data))?$data[0]->id_transaksi:"";
        $this->data['kode_pelanggan'] =   (!empty($data))?$data[0]->kode_pelanggan:"";
        $this->data['kirim_via'] =   (!empty($data))?$data[0]->kirim_via:"";
        $this->data['jenis_pengiriman'] =   (!empty($data))?$data[0]->jenis_pengiriman:"";
        $this->data['catatan'] =   (!empty($data))?$data[0]->catatan:"";
        $this->data['jenis_pembayaran'] =   (!empty($data))?$data[0]->jenis_pembayaran:"";
       
        $this->data['asal'] =   (!empty($data))?$data[0]->asal:"";
        $this->data['KDPROP_asal'] =   (!empty($data))?$data[0]->KDPROP_asal:"";
        $this->data['KDKAB_asal'] =   (!empty($data))?$data[0]->KDKAB_asal:"";
        $this->data['KDPROP_tujuan'] =   (!empty($data))?$data[0]->KDPROP_tujuan:"";
        $this->data['KDKAB_tujuan'] =   (!empty($data))?$data[0]->KDKAB_tujuan:"";
        $this->data['tipe_berat'] =   (!empty($data))?$data[0]->tipe_berat:"";
        $this->data['alamat'] =   (!empty($data))?$data[0]->alamat:"";
        $this->data['alamat_tujuan'] =   (!empty($data))?$data[0]->alamat_tujuan:"";
       
        $this->data['status']     =   (!empty($data))?$data[0]->status:""; 
        $this->data['posting']    =   (!empty($data))?$data[0]->posting:"";  
        $this->data['waktu_input']    =   (!empty($data))?$data[0]->created_at:"";
        $this->data['barang']    =   $barang;
        
        $this->data['content'] = 'admin/order/detail_v'; 
        $this->data['pelanggan'] = $this->customer_model->getAllById();
        $this->data['data_provinsi'] = $this->wilayah_model->getAllProvince();  
       
        $this->load->view('admin/layouts/page',$this->data); 
      }  
    }   
  } 


  public function exportCSV()
    { 
       // file name 
       $filename = 'Pelanggan'.date('Ymd').'.csv'; 
       header("Content-Description: File Transfer"); 
       header("Content-Disposition: attachment; filename=$filename"); 
       header("Content-Type: application/csv; ");
       
       // get data 
       
       $datas = $this->customer_model->getAllById();

       // file creation 
       $file = fopen('php://output', 'w');
     
       $header = array("ID CUSTOMER","NAMA","EMAIL","JENIS KELAMIN","TELPON","ALAMAT"); 
       fputcsv($file, $header);
       foreach ($datas as $line){ 
         fputcsv($file,array($line->id_customer,$line->nama,$line->email,$line->jk,$line->telp,$line->alamat));
       }
       fclose($file); 
       exit; 
      } 

public function destroy(){
    $response_data = array();
        $response_data['status'] = false;
        $response_data['msg'] = "";
        $response_data['data'] = array();   
    $id =$this->uri->segment(3);
    if(!empty($id)){
          $delete = $this->transaksi_model->delete(array("id_transaksi"=>$id));
          $delete_ukuran = $this->transaksi_ukuran_model->delete(array("id_transaksi"=>$id));
         
          $response_data['data'] = $data; 
          $response_data['status'] = true;
    }else{
      $response_data['msg'] = "ID Harus Diisi";
    }
    
        echo json_encode($response_data); 
  }

}
?>