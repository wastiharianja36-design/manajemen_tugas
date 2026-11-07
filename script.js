function konfirmasiHapus(){

    return confirm("Yakin mau hapus tugas ini?");

}
function cekDeadline(jumlah){

    if(jumlah > 0){

        alert(
            "⚠️ Ada " + jumlah + 
            " tugas yang terlambat!"
        );

    }

}
function cekBesok(jumlah){

    if(jumlah > 0){

        alert(
            "⏰ Besok ada " + jumlah +
            " tugas yang harus dikumpul!"
        );

    }

}