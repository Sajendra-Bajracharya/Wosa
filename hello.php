<?php
 include('../config/include.php');

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Food Website</title>
    <link rel="stylesheet" href="style1.css" />
    
    
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
      integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
  </head>
  <body>
    <section id="Home">
      <nav class="navbar">
        <div class="logo">
          <img src="image/logo.png" />
        </div>

        <ul>
          <li><a href="<?php echo $home; ?>fweb/">Home</a></li>
          <li><a href="#About">About</a></li>
          <li><a href="#Menu">Menu</a></li>
          <li><a href="#Gallary">Gallery</a></li>
          <li><a href="#Review">Review</a></li>
          <!-- <li><a href="#Order">Order</a></li> -->
          <li><a href="<?php echo $home; ?>admin/userloginpage.php">Login</a></li>
        </ul>
        <!-- <div class="search-container">
          <form action="">
            <input type="text" placeholder="Search.." name="search" >
            <button type="submit"><i class="fa fa-search"></i>Search</button>
          </form>
        </div> -->
      
        

        
          
      </nav>
    
      <div class="main">
        <div class="men_text">
          <br><br><br>
          <h1>Get Fresh<span>Food</span><br />in a Easy Way</h1>
        </div>

        <div class="main_image">
          <img src="image/main_img.png" />
        </div>
      </div>

      <p>
        
      </p>

      <!-- <div class="main_btn">
        <a href="#">Order Now</a>
        <i class="fa-solid fa-angle-right"></i>
      </div> -->
    </section>

    <!--About-->

    <div class="about" id="About">
      <div class="about_main">
        <div class="image">
          <img src="image/Food-Plate.png" />
        </div>

        <div class="about_text">
          <h1><span>About</span>Us</h1>
          <h3>Why Choose us?</h3>
          <p>
            Lorem ipsum dolor sit amet consectetur, adipisicing elit. Expedita,
            est. Doloremque sapiente veritatis laboriosam corrupti optio et
            maxime. Ad, amet explicabo eaque labore cupiditate quasi nostrum
            nemo recusandae id quibusdam? Lorem ipsum dolor sit amet consectetur
            adipisicing elit. Doloremque ab, dolores pariatur cum
            exercitationem, illo nisi veritatis vitae ea deleniti fugiat
            quisquam tempora, accusantium corrupti excepturi optio. Inventore,
            cupiditate recusandae.
          </p>
        </div>
      </div>

      
    </div>

    <!--Menu-->

    <div class="menu" id="Menu">
      <h1>Our<span>Menu</span></h1>
      <div class="menu_box">
      <?php
     
        
          $sql2="SELECT * FROM tbl_food WHERE active='Yes' AND featured='Yes' Limit 4";

          $res2=mysqli_query($conn,$sql2);

          $count2=mysqli_num_rows($res2);

          if($count2>0){
            //food available
            while($row=mysqli_fetch_assoc($res2)){

              $id=$row['id'];
              $title=$row['title'];
              $price=$row['price'];
              $description=$row['description'];
              $image_name=$row['image_name'];
              ?>

                
                  <div class="menu_card">
                    <div class="menu_image">
                    <?php
                      //check id image is available or not 
                        if($image_name==""){
                          echo "<div class='error'>Image not available</div>";
                        }else{
                          ?>
                          
                          <img
                            src="<?php $home; ?>../images/food/<?php echo $image_name; ?>"
                            alt="Pizza"
                            class="img-responsive img-curve"
                          />
                          
                          <?php
                        }

                      ?>
                    </div>

                   

                    <div class="menu_info">
                      <h2><?php echo $title; ?></h2>
                      <p>
                        <?php echo $description; ?>
                      </p>
                      <h3>Rs.<?php echo $price; ?></h3>
                      <div class="menu_icon">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star-half-stroke"></i>
                      </div>
                      <a href="<?php echo $home; ?>admin/userloginpage.php" class="menu_btn">Order Now</a>
                    </div>
                  </div>

                
          <?php
            }
                
          

          }else{
            //food not available
            echo "<div class='error'>Food not available</div>";
          }
        
          ?>
        </div>
        
       <div class="order_btn">
        <a href="<?php echo $home; ?>admin/userloginpage.php" class="btn ">See All Foods</a>
       </div>
        

    </div>
  

    <!--Gallary-->

    <div class="gallary" id="Gallary">
      <h1>Our<span>Gallery</span></h1>
      <div class="gallary_image_box">
        
      <?php
      
      $sql="SELECT * FROM tbl_category WHERE active='Yes' AND featured='Yes' LIMIT 3 ";

          $res=mysqli_query($conn,$sql);

          $count=mysqli_num_rows($res);

          if($count>0){
            // echo "category available";
            while($row=mysqli_fetch_assoc($res)){

              $id=$row['id'];
              $title=$row['title'];
              $image_name=$row['image_name'];
              ?>

      
        <div class="gallary_image">
        <?php
                      //check id image is available or not 
                        if($image_name==""){
                          echo "<div class='error'>Image not available</div>";
                        }else{
                          ?>
                          
                          <img
                            src="<?php $home; ?>../images/category/<?php echo $image_name; ?>"
                            alt="hhlh"
                            class="img-responsive img-curve"
                          />
                          
                          <?php
                        }

                      ?>
          

          <h3><?php echo $title; ?></h3>
          
          <a href="<?php echo $home; ?>admin/userloginpage.php" class="gallary_btn">Order Now</a>
        </div>

        
      
      <?php

            }

          }else{
            //food not available
            echo "<div class='error'>Food not available</div>";
          }
        
        ?>


      </div>

    </div>

    <!--Review-->

    <div class="review" id="Review">
      <h1>Customer<span>Review</span></h1>

      <div class="review_box">
        <div class="review_card">
          <div class="review_profile">
            <img src="image/review_1.png" />
          </div>

          <div class="review_text">
            <h2 class="name">John Deo</h2>

            <div class="review_icon">
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star-half-stroke"></i>
            </div>

            <div class="review_social">
              <i class="fa-brands fa-facebook-f"></i>
              <i class="fa-brands fa-instagram"></i>
              <i class="fa-brands fa-twitter"></i>
              <i class="fa-brands fa-linkedin-in"></i>
            </div>

            <p>
              Lorem ipsum dolor sit, amet consectetur adipisicing elit.
              Repellendus quam facere blanditiis in fugiat tempore
              necessitatibus aliquid. Id adipisci, rem corrupti asperiores
              distinctio delectus quae quia tenetur totam laboriosam quam. Lorem
              ipsum, dolor sit amet consectetur adipisicing elit. Dolores soluta
              ullam ipsa voluptates
            </p>
          </div>
        </div>

        <div class="review_card">
          <div class="review_profile">
            <img src="image/review_2.png" />
          </div>

          <div class="review_text">
            <h2 class="name">John Deo</h2>

            <div class="review_icon">
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star-half-stroke"></i>
            </div>

            <div class="review_social">
              <i class="fa-brands fa-facebook-f"></i>
              <i class="fa-brands fa-instagram"></i>
              <i class="fa-brands fa-twitter"></i>
              <i class="fa-brands fa-linkedin-in"></i>
            </div>

            <p>
              Lorem ipsum dolor sit, amet consectetur adipisicing elit.
              Repellendus quam facere blanditiis in fugiat tempore
              necessitatibus aliquid. Id adipisci, rem corrupti asperiores
              distinctio delectus quae quia tenetur totam laboriosam quam. Lorem
              ipsum, dolor sit amet consectetur adipisicing elit. Dolores soluta
              ullam ipsa voluptates
            </p>
          </div>
        </div>

        <div class="review_card">
          <div class="review_profile">
            <img src="image/review_3.png" />
          </div>

          <div class="review_text">
            <h2 class="name">John Deo</h2>

            <div class="review_icon">
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star-half-stroke"></i>
            </div>

            <div class="review_social">
              <i class="fa-brands fa-facebook-f"></i>
              <i class="fa-brands fa-instagram"></i>
              <i class="fa-brands fa-twitter"></i>
              <i class="fa-brands fa-linkedin-in"></i>
            </div>

            <p>
              Lorem ipsum dolor sit, amet consectetur adipisicing elit.
              Repellendus quam facere blanditiis in fugiat tempore
              necessitatibus aliquid. Id adipisci, rem corrupti asperiores
              distinctio delectus quae quia tenetur totam laboriosam quam. Lorem
              ipsum,
            </p>
          </div>
        </div>

        <div class="review_card">
          <div class="review_profile">
            <img src="image/review_4.png" />
          </div>

          <div class="review_text">
            <h2 class="name">John Deo</h2>

            <div class="review_icon">
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star-half-stroke"></i>
            </div>

            <div class="review_social">
              <i class="fa-brands fa-facebook-f"></i>
              <i class="fa-brands fa-instagram"></i>
              <i class="fa-brands fa-twitter"></i>
              <i class="fa-brands fa-linkedin-in"></i>
            </div>

            <p>
              Lorem ipsum dolor sit, amet consectetur adipisicing elit.
              Repellendus quam facere blanditiis in fugiat tempore
              necessitatibus aliquid. Id adipisci, rem corrupti asperiores
              distinctio delectus quae quia tenetur totam laboriosam quam. Lorem
              ipsum,
            </p>
          </div>
        </div>
      </div>
    </div>
  

    <!--Order-->

    <!-- <div class="order" id="Order">
      <h1><span>Order</span>Now</h1>

      <div class="order_main">
        <div class="order_image">
          <img src="image/order_image.png" />
        </div>

        <form action="#">
          <div class="input">
            <p>Name</p>
            <input type="text" placeholder="you name" />
          </div>

          <div class="input">
            <p>Email</p>
            <input type="email" placeholder="you email" />
          </div>

          <div class="input">
            <p>Number</p>
            <input placeholder="you number" />
          </div>

          <div class="input">
            <p>How Much</p>
            <input type="number" placeholder="how many order" />
          </div>

          <div class="input">
            <p>You Order</p>
            <input placeholder="food name" />
          </div>

          <div class="input">
            <p>Address</p>
            <input placeholder="you Address" />
          </div>

          <a href="#" class="order_btn">Order Now</a>
        </form>
      </div>
    </div> -->

    <!--Team-->
    <br /><br /><br /><br><br><br>
    <br /><br /><br /><br><br><br>
    <div class="team">
      <h1>Our<span>Team</span></h1>

      <div class="team_box">
        <div class="profile">
          <img src="image/chef1.png" />

          <div class="info">
            <h2 class="name">Chef</h2>
            <p class="bio">
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
            </p>

            <div class="team_icon">
              <i class="fa-brands fa-facebook-f"></i>
              <i class="fa-brands fa-twitter"></i>
              <i class="fa-brands fa-instagram"></i>
            </div>
          </div>
        </div>

        <div class="profile">
          <img src="image/chef2.png" />

          <div class="info">
            <h2 class="name">Chef</h2>
            <p class="bio">
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
            </p>

            <div class="team_icon">
              <i class="fa-brands fa-facebook-f"></i>
              <i class="fa-brands fa-twitter"></i>
              <i class="fa-brands fa-instagram"></i>
            </div>
          </div>
        </div>

        <div class="profile">
          <img src="image/chef3.jpg" />

          <div class="info">
            <h2 class="name">Chef</h2>
            <p class="bio">
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
            </p>

            <div class="team_icon">
              <i class="fa-brands fa-facebook-f"></i>
              <i class="fa-brands fa-twitter"></i>
              <i class="fa-brands fa-instagram"></i>
            </div>
          </div>
        </div>

        <div class="profile">
          <img src="image/chef4.jpg" />

          <div class="info">
            <h2 class="name">Chef</h2>
            <p class="bio">
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
            </p>

            <div class="team_icon">
              <i class="fa-brands fa-facebook-f"></i>
              <i class="fa-brands fa-twitter"></i>
              <i class="fa-brands fa-instagram"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--Footer-->

    <footer>
      <div class="footer_main">
        <div class="footer_tag">
          <h2>Location</h2>
          <p>Kathmandu</p>
          <p>Pokhara</p>
          <p>Butwal</p>
          <p>Bhaktapur</p>
          <p>Patan</p>
        </div>

        <div class="footer_tag">
          <h2>Payment Method</h2>
            <p>Cash on Delivery</p>
        </div>

        <div class="footer_tag">
          <h2>Contact</h2>
          <p>9876543210</p>
          <p>rojendangol@gmail.com</p>
          <p>foodhub@gmail.com</p>
        </div>

        <div class="footer_tag">
          <h2>Our Service</h2>
          <p>Fast Delivery</p>
          <p>Easy Payments</p>
          <p>24 x 7 Service</p>
        </div>

        <div class="footer_tag">
          <h2>Follows</h2>
          <ul type="none">
          <li>
            <a href="https://www.facebook.com"><img src="../images/fb.png" /></a>
          </li>
          <li>
            <a href="https://www.instagram.com"><img src="../images/ins.png" /></a>
          </li>
          <li>
            <a href="https://twitter.com"><img src="../images/twi.png" /></a>
          </li>
        </ul>
        </div>
      </div>

      <!-- <p class="end">
        Design by<span
          ><i class="fa-solid fa-face-grin"></i> Rojen Dangol</span
        >
      </p> -->
    </footer>
  </body>
</html>
