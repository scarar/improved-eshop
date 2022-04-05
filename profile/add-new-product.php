<?php
// Check if user is not logged in, redirect him to login page
session_start();
if($_SESSION['user']==''){
    header("Location:http://".$_SERVER['HTTP_HOST']."/login.php");
    exit;
}else{
    $db_array = include("../../../../etc/return_db_array.php");
    $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
    if (!$conn) {
        die("ERROR: Unable to connect: " . $conn->connect_error);
    }

    if(isset($_POST['add_new_product'])){
        if(isset($_POST['requires_fe'])){
            $FE = 1;
        }else{
            $FE = 0;
        }
//        echo 'YES';
        if($_POST['title'] == '' || $_POST['price'] == '' || htmlspecialchars($_POST['short_description']) == ''
            || $_POST['vendor'] == '' || $_POST['product_type'] == '') {
//            echo $_POST['title'];
//            echo $_POST['price'];
//            echo htmlspecialchars($_POST['short_description']);
//            echo htmlspecialchars($_POST['description']);
//            echo $_POST['meta_tags'];
//            echo $_POST['vendor'];
//            echo $_POST['product_type'];

            //validate inputs
            $message = '<div id="message" class="alert alert-warning">';
            $message .= 'All required fields must be filled';
            $message .= '</div>';
        }else{
//            echo 'Again';
//            echo 'FE:'.$_POST['requires_fe'];
//            echo 'file:'.$_FILES['p_image'];
//            echo isset($_FILES['p_image']);
            if(isset($_FILES['p_image']) AND $_FILES['p_image']['tmp_name'] !=''){
                //file was set to upload
                //props
                $photo_file_name = $_FILES['p_image']['name'];
//                echo 'P img name:'.$photo_file_name;
                $photo_file_tmp = $_FILES['p_image']['tmp_name'];
                $photo_file_size = $_FILES['p_image']['size'];
                $photo_file_error = $_FILES['p_image']['error'];
                //get extension
                $photo_file_ext = explode('.', $photo_file_name);
                $photo_file_ext = strtolower(end($photo_file_ext));
                $allowed   = array('jpg','jpeg','png','gif');
//                echo 'ext:'.$photo_file_ext;
                if(in_array( $photo_file_ext, $allowed )){
                    if( $photo_file_error === 0 ){
//                    echo 'file size:'.$photo_file_size;
                        if($photo_file_size <= 500048 ){
                            $photo_name_new = str_replace('.','-',uniqid('',true)). '.' . $photo_file_ext;
                            $photo_dest = 'uploads/' . $photo_name_new;
//                            echo 'New name:'.$photo_name_new;
                            chmod("uploads/", 0744);
                            if(move_uploaded_file( $photo_file_tmp , $photo_dest)){
                                chmod("uploads/", 0755);

                            }else{
                                $message = '<div id="message" class="alert alert-success">';
                                $message .= 'Error Uploading product image.';
                                $message .= '</div>';
                            }
                        }
                    }
                }

            }else{
//                echo 'NOT SET';
            }
//            echo $_POST['title'];
//            echo $_POST['price'];
//            echo htmlspecialchars($_POST['shortdescription']);
//            echo htmlspecialchars($_POST['description']);
//            echo $_POST['meta_tags'];
//            echo $_POST['vendor'];
//            echo $_POST['producttype'];

            //insert image as well => pending
            $sql = 'INSERT INTO `products` ( title, image,price,description,short_description, meta_tags, vendor ,requires_fe, product_type,category_id ) VALUES ("'.$_POST["title"].'", "'.$photo_name_new.'", "'.$_POST["price"].'", "'.$_POST["short_description"].'", "'.$_POST["description"].'", "'.$_POST["meta_tags"].'", "'.$_POST["vendor"].'", "'.$FE.'", "'.$_POST["product_type"].'", "'.$_POST["category"].'")';
            echo 'Quantity:'.$_POST['quantity'];
            if (!mysqli_query($conn, $sql)) {
                $message = '<div id="message" class="alert alert-warning">';
                $message = 'Error Uploading product:';
                $message = mysqli_error($conn);
                $message = '</div>';
            } else {
                $last_id = $conn->insert_id;
                $message = '<div id="message" class="alert alert-success">';
                $message = 'Product Uploaded!';
                $message = '</div>';
            }
//            echo 'Last id:'.$last_id;

            //insert product quantity
            $sql = 'INSERT INTO `product_meta` ( product_id, meta_key, meta_value ) VALUES ('.$last_id.', \'quantity\', '.$_POST["quantity"].')';

            if (!mysqli_query($conn, $sql)) {
                $message .= '<div id="message" class="alert alert-warning">';
                $message .= 'Error Uploading product:';
                $message .= mysqli_error($conn);
                $message .= '</div>';
            } else {
                $message .= '<div id="message" class="alert alert-success">';
                $message .= 'Product Uploaded!';
                $message .= '</div>';
            }

        }
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="../style.css" media="all" rel="stylesheet" />
</head>
<body>

<?php
// get main menu
include('../parts/main_menu.php');
?>
<div class="container-fluid text-center main">
    <div class="col-sm-3 text-center sidebar">
        <?php
        include('../parts/sidebar.php');
        ?>
    </div>
    <div style="padding-top: 0;" class="col-sm-9 text-left main">
        <h3 style="margin-top: 0;"><span class="glyphicon glyphicon-plus-sign"></span> Add New Product</h3>

        <form class="form" enctype="multipart/form-data" action="add-new-product.php" method="post">
            <?php echo $message;?>
            <label class="return_message" id="return_message"></label>
            <label for="title">Title:</label>
            <input type="text" class="form-control" name="title" id="title"> value="<?php echo $_POST['title'];?>"/>
            <br/>
            <label for="price">Price:</label>
            <input type="text" class="form-control" name="price" id="price"> value="<?php echo $_POST['price'];?>"/>
            <br/>
            <label for="file">Upload Product image*:</label>
            <input name="p_image" style="width: 100%;" type="file" id="file">
            <br/>
            <label for="short_description">Short Description:</label>
            <textarea style="min-width: 100%;margin-top: 10px;" id="short_description" name="short_description" maxlength="250" ></textarea>
            <br/>
            <label for="description">Description:</label>
            <textarea style="min-width: 100%;margin-top: 10px;" id="description" name="description" maxlength="500" ></textarea>
            <br/>
            <br/>
            <label for="vendor">Quantity:</label>
            <input class="form-control" type="text" id="quantity" name="quantity"/>
            <br/>
            <label for="meta_tags">Meta Tags: (Comma Separated)</label>
            <input type="text" class="form-control" id="meta_tags" name="meta_tags" value="<?php echo $_GET['meta_tags'];?>"/>
            <br/>
            <label for="vendor">Vendor:</label>
            <label class="form-control"><input type="hidden" id="vendor" name="vendor" value="<?php echo $_SESSION['user'];?>"/><?php echo $_SESSION['user'];?></label>
            <br/>
            <label for="product_type">Product Type:</label>
            <br>
            <select class="form-control" name="product_type" id="product_type" style="min-width:200px;height: 35px;border-radius: 4px;border: 1px solid silver;padding: 5px;line-height: 20px;">
                <option value="physical">Physical</option>
                <option value="virtual">Virtual</option>
            </select>
            <br/>
            <div>
                <b>Choose Category:</b>
                <br>                <select class="form-control" name="category" style="min-width:200px;height: 35px;border-radius: 4px;border: 1px solid silver;padding: 5px;line-height: 20px;">

                <?php
                $sql2 = 'SELECT * FROM categories';
                $cat_result = $conn->query($sql2);
                if ($cat_result->num_rows > 0) {
                    while ($cat_row = $cat_result->fetch_assoc()) {
                        echo '<option value="'.$cat_row["id"].'">'.$cat_row["title"].'</option>';
                    }
                }
                ?>
                </select>
            </div>
            <br/>
            <label for="requires_fe" style="float: left;">Requires FE:</label>
            <input type="checkbox" class="form-control" checked style="width: 100px;display: block;height: 10px;" id="requires_fe" name="requires_fe" value="<?php echo $_POST['requires_fe'];?>"/>
            <br/>
            <br/>
            <input id="add_new_product" name="add_new_product" type="submit" class="btn btn-lg btn-primary" value="Add Product">
            <br/> <br/>
            View all products you added: <a id="my_products" href="http://<?php echo $_SERVER["HTTP_HOST"]?>/vendors/vendor_products">My Products</a>
        </form>

    </div>
</div>
<br/>
<br/>
<footer class="container-fluid text-center">
    <p>Footer Text</p>
</footer>

</body>
</html>
