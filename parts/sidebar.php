<b style="border-bottom:#f5f5f5 1px solid;
display: block;
padding-bottom: 8px;">Categories:</b>
<ul class="nav" style="text-align: left;">

    <?php
    $sql = 'SELECT * FROM categories';
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
    // if username was found, match the password
    // get row data
        while ($cat_row = $result->fetch_assoc()) {
            echo '<li><a title="'.$cat_row["title"].'" href="http://'.$_SERVER['HTTP_HOST'].'?category='.$cat_row["id"].'">'.$cat_row["title"].'</a></li>';
        }
    }
    ?>
</ul>

