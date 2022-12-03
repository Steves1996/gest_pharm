<?php
$dfrom = isset($_GET['date_from']) ? $_GET['date_from'] : date("Y-m-d", strtotime(date("Y-m-d") . " -1 week"));
$dto = isset($_GET['date_to']) ? $_GET['date_to'] : date("Y-m-d");
?>
<div class="card rounded-0 shadow">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Inventory Report</h3>
    </div>
    <div class="card-body">
        <h5>Filter</h5>
        <div class="row align-items-end">
            <div class="form-group col-md-2">
                <label for="date_from" class="control-label">Date From</label>
                <input type="date" name="date_from" id="date_from" value="<?php echo $dfrom ?>"
                       class="form-control rounded-0">
            </div>
            <div class="form-group col-md-2">
                <label for="date_to" class="control-label">Date To</label>
                <input type="date" name="date_to" id="date_to" value="<?php echo $dto ?>"
                       class="form-control rounded-0">
            </div>
            <div class="form-group col-md-4 d-flex">
                <div class="col-auto">
                    <button class="btn btn-primary rounded-0" id="filter" type="button"><i class="fa fa-filter"></i>
                        Filter
                    </button>
                    <button class="btn btn-success rounded-0" id="print" type="button"><i class="fa fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
        <hr>
        <div class="clear-fix mb-2"></div>
        <div id="outprint">
            <table class="table table-hover table-striped table-bordered">
                <colgroup>
                    <col width="5%">
                    <col width="20%">
                    <col width="25%">
                    <col width="10%">
                    <col width="20%">
                    <col width="20%">
                </colgroup>
                <thead>
                <tr>
                    <th class="text-center p-0">#</th>
                    <th class="text-center p-0">Product Code</th>
                    <th class="text-center p-0">Nom du Produit</th>
                    <th class="text-center p-0">Qte</th>
                    <th class="text-center p-0">Qte Vendu</th>
                    <th class="text-center p-0">Qte en stock</th>
                    <th class="text-center p-10">Qte Reel</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT p.*,c.name as cname FROM `product_list` p inner join `category_list` c on p.category_id = c.category_id where p.status = 1 and p.delete_flag = 0 order by `name` asc";
                $qry = $conn->query($sql);
                $i = 1;
                while($row = $qry->fetch_assoc()):
                    $stock_in = $conn->query("SELECT sum(quantity) as `total` FROM `stock_list` where unix_timestamp(CONCAT(`expiry_date`, ' 23:59:59')) >= unix_timestamp(CURRENT_TIMESTAMP) and product_id = '{$row['product_id']}' ")->fetch_array()['total'];
                    $stock_out = $conn->query("SELECT sum(quantity) as `total` FROM `transaction_items` where product_id = '{$row['product_id']}' ")->fetch_array()['total'];
                    $stock_in = $stock_in > 0 ? $stock_in : 0;
                    $stock_out = $stock_out > 0 ? $stock_out : 0;
                    $qty = $stock_in-$stock_out;
                    $qty = $qty > 0 ? $qty : 0;
                    ?>
                    <tr>
                        <td class="text-center p-0"><?php echo $i++; ?></td>
                        <td class="py-0 px-1"><?php echo $row['product_code'] ?></td>
                        <td class="py-0 px-1"><?php echo $row['name'] ?></a></td>
                        <td class="py-0 px-1 text-end"><?php echo $stock_in ?></td>
                        <td class="py-0 px-1 text-end"><?php echo $stock_out ?></td>
                        <td class="py-0 px-1 text-end"><?php echo $qty ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($qry->num_rows <= 0): ?>
                    <th colspan="6">
                        <center>No Transaction listed in selected date.</center>
                    </th>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('.view_data').click(function () {
            uni_modal('Receipt', "view_receipt.php?view_only=true&id=" + $(this).attr('data-id'), '')
        })
        $('#filter').click(function () {
            location.href = "./?page=inventory_report&date_from=" + $('#date_from').val() + "&date_to=" + $('#date_to').val();
        })

        $('table td,table th').addClass('align-middle')

        $('#print').click(function () {
            var h = $('head').clone()
            var p = $('#outprint').clone()
            var el = $('<div>')
            el.append(h)
            if ('<?php echo $dfrom ?>' == '<?php echo $dto ?>') {
                date_range = "<?php echo date('M d, Y', strtotime($dfrom)) ?>";
            } else {
                date_range = "<?php echo date('M d, Y', strtotime($dfrom)) . ' - ' . date('M d, Y', strtotime($dto)) ?>";
            }
            el.append("<div class='text-center lh-1 fw-bold'>Pharmacy's Sales Report<br/>As of<br/>" + date_range + "</div><hr/>")
            p.find('a').addClass('text-decoration-none')
            el.append(p)
            var nw = window.open("", "", "width=500,height=900")
            nw.document.write(el.html())
            nw.document.close()
            setTimeout(() => {
                nw.print()
                setTimeout(() => {
                    nw.close()
                }, 150);
            }, 200);
        })
        // $('table').dataTable({
        //     columnDefs: [
        //         { orderable: false, targets:3 }
        //     ]
        // })
    })

</script>