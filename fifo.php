<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>FIFO Impelementasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--<link rel="stylesheet" type="text/css" media="screen" href="main.css" />-->
    <!--<script src="main.js"></script>-->
</head>
<body>
    <?php
        mysql_connect('localhost', 'root', '') or die('Tidak terkoneksi ke database');
        mysql_select_db('db_alpro') or die('Tidak terkoneksi ke database');

    ?>


    <h2>Stok Barang Masuk (IN)</h2>
    <table border="1">
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah Stok</th>
            <th>Tanggal Masuk</th>
        </tr>

        <?php
            $sql = mysql_query("SELECT a.tglMasuk, a.qty, b.* FROM barang_stok a 
                                LEFT JOIN barang b ON a.kodeBrg = b.kodeBrg ORDER BY a.tglMasuk ASC") or die(mysql_error());


            $no = 1;
            while ($a = mysql_fetch_assoc($sql)) {
                echo"<tr>
                    <td>".$no."</td>
                    <td>".$a['kodeBrg']."</td>
                    <td>".$a['nmBrg']."</td>
                    <td>".$a['qty']."</td>
                    <td>".$a['tglMasuk']."</td>
                </tr>";
            }
        ?>
    </table>


    <h1>Stok OUT</h1>

    <form action="" method="POST">
        <p>
            <select name="kodeBrg">
                <?php
                    $sqlBrg = mysql_query("SELECT * FROM barang") or die(mysql_error());

                    while($b = mysql_fetch_assoc($sqlBrg)) {
                        echo"<option value='".$b['kodeBrg']."'>".$b['kodeBrg']." - ".$b['nmBrg']."</option>";
                    }
            
                ?>
            </select>
            <input type="text" name="qty" size="10">
            <input type="submit" name="submit" value="Out">
        </p>
    </form>
    
    <h2>Stok Barang Masuk (OUT)</h2>

    <table border="1">
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah Stok</th>
            <th>Tanggal Keluar</th>
        </tr>

        <?php
            $sql = mysql_query("SELECT a.tglKeluar, a.qty, b.* FROM barang_jual a 
                                LEFT JOIN barang b ON a.kodeBrg = b.kodeBrg") or die(mysql_error());


            $no = 1;
            while ($a = mysql_fetch_assoc($sql)) {
                echo"<tr>
                    <td>".$no."</td>
                    <td>".$a['kodeBrg']."</td>
                    <td>".$a['nmBrg']."</td>
                    <td>".$a['qty']."</td>
                    <td>".$a['tglKeluar']."</td>
                </tr>";
            }
        ?>
    </table>


    <?php
        if (isset($_POST['submit'])) {
            $kodeBrg = $_POST['kodeBrg'];
            $qty = $_POST['qty'];

            if (cek_stok($kodeBrg, $qty) == TRUE) {
                mysql_query("INSERT INTO barang_jual(kodeBrg, qty) VALUES('$kodeBrg', $qty)") or die(mysql_error());
                dequeue($kodeBrg, $qty);
                
                echo"<script>
                    window.history.load();
                </script>";

                
            } else {
                echo "TIDAK CUKUP";
            }
        }

        //mengambil dari daftar antrian
        function cek_stok($kodeBrg, $jml)
        {
            $sqlBrg = mysql_query("SELECT SUM(qty) AS total FROM barang_stok WHERE kodeBrg = '".$kodeBrg."' GROUP BY kodeBrg");
            $b = mysql_fetch_assoc($sqlBrg);

            if ($b['total'] >= $jml) {
                return TRUE;
            } else {
                return FALSE;
            }
        }

        function dequeue($kodeBrg, $jml)
        {
            $j = $jml;
            $kd = $kodeBrg;
            $total = 0;

            $sqlBrg = mysql_query("SELECT * FROM barang_stok WHERE kodeBrg = '".$kodeBrg."' ORDER BY tglMasuk ASC");
            while($b = mysql_fetch_assoc($sqlBrg)) {

                if($b['qty'] >= $j) {
                    mysql_query("UPDATE barang_stok SET qty = qty - ".$j." 
                                WHERE kodeBrg = '".$kd."' AND tglMasuk = '".$b['tglMasuk']."'");
                    //exit;
                    return;
                } else {
                    if($b['qty'] >= $j) {
                        $j2 = $b['qty'] - $j;

                        ///exit;
                        return;
                    } else {
                        $j2 = $b['qty'];
                        $j = $j - $j2;
                    }

                    mysql_query("UPDATE barang_stok SET qty = qty - ".$j2." WHERE kodeBrg = '".$kd."' 
                                AND tglMasuk = '".$b['tglMasuk']."'");
                    
                }

            }
        }


    ?>
</body>
</html>