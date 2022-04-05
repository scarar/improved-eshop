<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">Logo</a>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav">
                <?php
                if(isset($_SESSION['user'])){
                    ?>
                    <!--<li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/orders"><span class="glyphicon glyphicon-shopping-cart"></span>&nbsp;Orders</a></li>
                -->
                <?php }
                ?>
                <?php
                if(!isset($_SESSION['user'])){
                ?>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/login"><span class="glyphicon glyphicon-log-in"></span>&nbsp;Login</a></li>
                <?php }?>
                <?php
                if(isset($_SESSION['user'])){
                    ?>
                    <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/"><i class="glyphicon glyphicon-home"></i> Home</a></li>
                    <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/add-new-product"><span class="glyphicon glyphicon-plus-sign"></span> Add New Product</a></li>
                    <li><a href="#"><span class="glyphicon glyphicon-user"></span>&nbsp;Profile
                            <ul class="dropdown-menu">
                                <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/settings"><span class="glyphicon glyphicon-cog"></span> Settings</a></li>
                            </ul>
                        </a>
                    </li>
                <?php }
                if( !isset($_SESSION['user']) ){
                    ?>
                    <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/register"><i class="glyphicon glyphicon-plus-sign"></i></span> Register</a></li>
                <?php }
                if( isset($_SESSION['user']) ){
                ?>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/logout"><i class="glyphicon glyphicon-off"></i></span> Logout</a></li>
                <?php }
                ?>
            </ul>
        </div>
    </div>
</nav>
