define([
    "jQuery",
    "jQueryUI",
    "bootstrap", 
    "bootstrapDatepicker", 
    "sidebar",
    "datatables",
    "datatablesBootstrap",
    "select2",
    "jqvalidate",
    ], function (
    $,
    jQueryUI,
    bootstrap,
    bootstrapDatepicker,
    highchart,
    sidebar,
    datatables,
    datatablesBootstrap,
    select2,
    jqvalidate,    
    ) { 
    return {  
        table:null,
        kdprop:$("#kdprop_selected").val(),
        kdkab:$("#kdkab_selected").val(),
        kdkec:$("#kdkec_selected").val(),
        kddesa:$("#kddesa_selected").val(),
        init: function () { 
            App.initFunc();
            App.initEvent();  
            App.initConfirm();
            App.locPicker();
            App.searchTable();
            App.resetSearch();
 
            $(".loading").hide();
        }, 
        initEvent : function(){   
            $('.select2').select2();

            App.table = $('#table').DataTable({
                "language": {
                    "search": "Cari",
                    "lengthMenu": "Tampilkan _MENU_ baris per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan _PAGE_ dari _PAGES_",
                    "infoEmpty": "Tidak ada data yang ditampilkan ",
                    "infoFiltered": "(pencarian dari _MAX_ total records)",
                    "paginate": {
                        "first":      "Pertama",
                        "last":       "Terakhir",
                        "next":       "Selanjutnya",
                        "previous":   "Sebelum"
                    },
                },
                "processing": true,
                "serverSide": true,
                "ajax":{
                    "url": App.baseUrl+"cabang/dataList",
                    "dataType": "json",
                    "type": "POST",
                }, 
                   
                "columns": [
                    { "data": "id" },  
                    { "data": "kdcbg" },  
                    { "data": "idcbg" }, 
                    { "data": "NMPROP" }, 
                    { "data": "NMKAB" }, 
                    { "data": "nama_cbg" }, 
                    { "data": "tipe_cbg" }, 
                    { "data": "stokis_cbg" }, 
                    { "data": "produksi_cbg" },
                    { "data": "gudang_pusat" },
                    { "data": "status"}                    
                ]      
            }); 
            
            $("#kdprop").select2({
                placeholder: "Please Select Provinsi",
            });
            
            $("#stokis").select2({
                placeholder: "Please Select Stokis",
            });

            $("#gudang").select2({
                placeholder: "Please Select Gudang",
            });

            $("#produksi").select2({
                placeholder: "Please Select Produksi",
            });
                        
            $("#kdkab").select2({
                placeholder: "Please Select Kota/Kabupaten",
            });

            $("#tipe").select2({
                placeholder: "Please Select Tipe",
            });          

        }, 

        locPicker:function(){
            $('#kdprop').on('change',function(){
                $.ajax({
                    url: App.baseUrl+'wilayah/get_kabupaten',
                    type: 'GET',
                    data: {
                        kdprop:$('#kdprop').val()
                    },
                    beforeSend:function(){
                        $('#kdkab').prop('disabled',true);
                        $('#kdkec').prop('disabled',true);
                        $('#kddesa').prop('disabled',true);
                    },
                    success:function(r){
                        var r = JSON.parse(r);
                        var element = '<option value="" selected>Semua Kab/ Kota</option>';
                        var data = r.data;

                        for (var i = 0; i < data.length; i++) {
                            if(App.kdkab !== undefined && App.kdkab == data[i].kdkab){
                                element+='<option value="'+data[i].kdkab+'" selected>'+data[i].nmkab+'</option>';

                            }else{
                                element+='<option value="'+data[i].kdkab+'">'+data[i].nmkab+'</option>';
                            }
                        }

                        $('#kdkab').html(element);
                        $('#kdkab').trigger("change");
                        $('#prop').val($('#kdprop option:selected').text());
                        $('#kdkec').html('<option value="" selected>Semua Kecamatan</option>');
                        $('#kddesa').html('<option value="" selected>Semua Kelurahan</option>');
                        $('#kdkab').prop('disabled',false);
                        $('#kdkec').prop('disabled',false);
                        $('#kddesa').prop('disabled',false);

                    }
                });
            });

            var kdprop = $("#kdprop").val();
            if(kdprop.length > 0){
                $('#kdprop').trigger("change");

            }
            $('#kdkab').on('change',function(){
                $.ajax({
                    url: App.baseUrl+'wilayah/get_kecamatan',
                    type: 'GET',
                    data: {
                        kdprop:$('#kdprop').val(),
                        kdkab:$('#kdkab').val()
                    },
                    beforeSend:function(){
                        $('#kdkec').prop('disabled',true);
                        $('#kddesa').prop('disabled',true);
                    },
                    success:function(r){
                        var element = '<option value="" selected>Semua Kecamatan</option>';
                        var r = JSON.parse(r);
                        var data = r.data;
                        for (var i = 0; i < data.length; i++) {
                            if(App.findKecSelected(data[i].kdkec)){
                                element+='<option value="'+data[i].kdkab+data[i].kdkec+'" selected>'+data[i].nmkec+'</option>'
                            }else{
                                element+='<option value="'+data[i].kdkab+data[i].kdkec+'">'+data[i].nmkec+'</option>'
                            }
                        }

                        $('#kdkec').html(element);
                        $('#kdkec').trigger("change");
                        $('#kab').val($('#kdkab option:selected').text());
                        $('#kddesa').html('<option value="" selected>Semua Kelurahan</option>');
                        $('#kdkec').prop('disabled',false);
                        $('#kddesa').prop('disabled',false);
                    }
                });
            });

            $('#kdkec').on('change',function(){
                $('#kdkec option[value=""]').prop('selected',false);
                $('#kddesa option[value=""]').prop('selected',false);
                $.ajax({
                    url: App.baseUrl+'wilayah/get_kelurahan',
                    type: 'GET',
                    data: {
                        kdprop:$('#kdprop').val(),
                        kdkab:$('#kdkab').val() ,
                        kdkec:$('#kdkec').val()
                    },
                    beforeSend:function(){
                        $('#kddesa').prop('disabled',true);
                    },
                    success:function(r){
                        var r = JSON.parse(r);
                        var element = '<option value="" selected>Semua Kelurahan</option>';
                        var data = r.data;
                        for (var i = 0; i < data.length; i++) {
                            if(App.findDesaSelected(data[i].kddesa)){
                                element+='<option value="'+data[i].kddesa+'" selected>'+data[i].nmdesa+'</option>'
                            }else{
                                element+='<option value="'+data[i].kddesa+'">'+data[i].nmdesa+'</option>'
                            }
                        }

                        $('#kddesa').html(element);

                        $('#kddesa').trigger("change");
                        $('#kec').val($('#kdkec option:selected').text());
                        $('#kddesa').prop('disabled',false);
                    }
                });

            });

        },
        searchTable:function(){ 
            $('#search').on('click', function () {
                console.log("SEARCH");
                var kdprop  = $("#kdprop").val();
                var kdkab   = $("#kdkab").val();
                var tipe    = $("#tipe").val();
                var nama    = $("#nama").val();
 
                App.table.column(1).search(kdprop,true,true);
                App.table.column(2).search(kdkab,true,true);
                App.table.column(3).search(tipe,true,true);
                App.table.column(4).search(nama,true,true);

                App.table.draw();
                
            }); 
        },
        resetSearch:function(){
            $('#reset').on( 'click', function () {
                $("#kdprop").val("");
                $("#kdkab").val("");
                $("#tipe").val("");
                $("#nama").val("");

                App.table.search( '' ).columns().search( '' ).draw();
            });
        },
        findKecSelected:function(kdkec){ 
            if(App.kdkec !== undefined){
                var array = JSON.parse(App.kdkec);  
                for (var i = 0; i < array.length; i++) { 
                   if(array[i] == kdkec){
                    return true;
                   }
                }
            }

            return false;          
        },
        initConfirm :function(){
            $('#table tbody').on( 'click', '.delete', function () {
                var url = $(this).attr("url");
                App.confirm("Apakah Anda Yakin Untuk Mengubah Ini?",function(){
                   $.ajax({
                      method: "GET",
                      url: url
                    }).done(function( msg ) {
                        App.table.ajax.reload(null,true);
                    });        
                })
            });
        }
    }
});