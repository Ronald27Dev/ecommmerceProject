<?php if(!class_exists('Rain\Tpl')){exit;}?>    <div class="footer-top-area">
        <div class="zigzag-bottom"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="footer-about-us">
                        <h2>Store</h2>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Perferendis sunt id doloribus vero quam laborum quas alias dolores blanditiis iusto consequatur, modi aliquid eveniet eligendi iure eaque ipsam iste, pariatur omnis sint! Suscipit, debitis, quisquam. Laborum commodi veritatis magni at?</p>
                        <div class="footer-social">
                            <a href="#" target="_blank"><i class="fa fa-facebook"></i></a>
                            <a href="#" target="_blank"><i class="fa fa-twitter"></i></a>
                            <a href="#" target="_blank"><i class="fa fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="footer-menu">
                        <h2 class="footer-wid-title">Navegação </h2>
                        <ul>
                            <li><a href="#">Minha Conta</a></li>
                            <li><a href="#">Meus Pedidos</a></li>
                            <li><a href="#">Lista de Desejos</a></li>
                        </ul>                        
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="footer-menu">
                        <h2 class="footer-wid-title">Categorias</h2>
                        <ul>
                            <?php require $this->checkTemplate("categories-menu");?>

                        </ul>                        
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="footer-newsletter">
                        <h2 class="footer-wid-title">Newsletter</h2>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Perferendis sunt id doloribus!</p>
                        <div class="newsletter-form">
                            <form action="#">
                                <input type="email" placeholder="Type your email">
                                <input type="submit" value="Subscribe">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End footer top area -->

    <div class="footer-bottom-area">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="copyright">
                        <p>&copy; 2024 Ronald Rodrigues. <a href="#" target="_blank">##</a></p>
                    </div>
                    <div class="admin">
                        <a href="/admin/login">Admin</a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="footer-card-icon">
                        <i class="fa fa-cc-discover"></i>
                        <i class="fa fa-cc-mastercard"></i>
                        <i class="fa fa-cc-paypal"></i>
                        <i class="fa fa-cc-visa"></i>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End footer bottom area -->

    <!-- Latest jQuery form server -->
    <script src="https://code.jquery.com/jquery.min.js"></script>

    <!-- Bootstrap JS form CDN -->
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

    <!-- jQuery sticky menu -->
    <script src="/res/site/js/owl.carousel.min.js"></script>
    <script src="/res/site/js/jquery.sticky.js"></script>

    <!-- jQuery easing -->
    <script src="/res/site/js/jquery.easing.1.3.min.js"></script>

    <!-- Main Script -->
    <script src="/res/site/js/main.js"></script>

    <!-- Slider -->
    <script type="text/javascript" src="/res/site/js/bxslider.min.js"></script>
    <script type="text/javascript" src="/res/site/js/script.slider.js"></script>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Select the navbar menu element
            let navbarMenu = document.querySelector(".navbar-nav");

            // Function to set the active menu item
            function setActiveMenuItem(clickedItem) {
                // Remove 'active' class from all menu items
                navbarMenu.querySelectorAll("li").forEach(function(item) {
                    item.classList.remove("active");
                });

                // Add 'active' class to the closest <li> element of the clicked <a>
                clickedItem.closest("li").classList.add("active");

                // Save the href of the active menu item in localStorage
                localStorage.setItem('activeMenuItem', clickedItem.getAttribute('href'));
            }

            // Add click event listener to the navbar menu
            navbarMenu.addEventListener("click", function(event) {
                // Check if the clicked element is an <a> tag
                if (event.target.tagName === 'A') {
                    setActiveMenuItem(event.target);
                }
            });

            // Retrieve the active menu item's href from localStorage
            let activeHref = localStorage.getItem('activeMenuItem');
            if (activeHref) {
                // Find the <a> tag with the href matching the stored value
                let activeLink = navbarMenu.querySelector(`a[href="${activeHref}"]`);
                if (activeLink) {
                    // Set the active menu item based on the stored href
                    setActiveMenuItem(activeLink);
                }
            }
        });
    </script>
</body>
</html>