<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link rel="stylesheet" href="css/default.css" />
    <link rel="stylesheet" href="css/index.css" />
  </head>
  <body>
    <!-- ══ HEADER ══ -->
    <header>
      <nav>
        <!-- Logo + Search -->
        <div class="logo-search">
          <!-- <a href="tel:0907876666">call us at</a> -->
          <a href="home.html">
            <img
              src="./images/logo.jpg"
              title="Arena Market"
              class="logo"
              alt="logo"
            />
          </a>
          <div class="search-box">
            <input type="text" placeholder="Search products or shops" />
            <svg
              class="search-icon"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
              />
            </svg>
            
          </div>
        </div>

        <!-- Middle Nav Links -->
        <div class="search-menu-box">
          <ul>
            <li><a href="#">Home</a></li>

            <li class="order-dropdown" id="orders-chevron">
              <a href="#">Orders</a>
              <svg
                class="chevron"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m19.5 8.25-7.5 7.5-7.5-7.5"
                />
              </svg>
              <div class="order-container" id="orders-dropdown">
                <ul>
                  <li>Help Centre</li>
                  <li>Contact us</li>
                  <li>Track an order</li>
                  <li>Cancel an Order</li>
                  <li>Return &amp; Refund</li>
                </ul>
              </div>
            </li>

            <li class="order-dropdown" id="help-chevron">
              <a href="#">Help</a>
              <svg
                class="chevron"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m19.5 8.25-7.5 7.5-7.5-7.5"
                />
              </svg>
              <div class="order-container" id="help-dropdown">
                <ul>
                  <li>Help Centre</li>
                  <li>Contact us</li>
                  <li>FAQs</li>
                  <li>Shipping Info</li>
                </ul>
              </div>
            </li>
          </ul>
        </div>

        <!-- Right Icons -->
        <div class="nav-drop-buttons">
          <div class="icons-box">
            <div class="wishlist">
              <a href="">
                <svg
                  class="nav-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="size-6"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"
                  />
                </svg>

                <span class="wishlist">Wishlist</span>
              </a>
              
            </div>

                <div class="wishlist">
               <a href="" title="Profile">
              <svg
                class="nav-icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                />
              </svg>

                <span class="account">My Account</span>
              </a>
            </div>

            

            <div class="nav-divider"></div>

            <a href="" title="Notifications">
              <svg
                class="nav-icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                />
              </svg>
            </a>

            <a href="" title="Settings">
              <svg
                class="nav-icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                />
              </svg>
            </a>

            <!-- Hamburger (mobile only) -->
            <div class="hamburger" id="hamburger-btn" title="Menu">
              <svg
                class="nav-icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                />
              </svg>
            </div>
          </div>
        </div>
      </nav>

      <!-- Mobile Dropdown -->
      <div class="dropdown-container" id="dropdown-menu">
        <ul class="menu">
          <li>
            <a href="#" class="link-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                />
              </svg>
              Home
            </a>
          </li>
          <li>
            <a href="#" class="link-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"
                />
              </svg>
              Shop
            </a>
          </li>
          <li>
            <a href="#" class="link-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5"
                />
              </svg>

              Categories
            </a>
          </li>
          <li>
            <a href="#" class="link-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m7.875 14.25 1.214 1.942a2.25 2.25 0 0 0 1.908 1.058h2.006c.776 0 1.497-.4 1.908-1.058l1.214-1.942M2.41 9h4.636a2.25 2.25 0 0 1 1.872 1.002l.164.246a2.25 2.25 0 0 0 1.872 1.002h2.092a2.25 2.25 0 0 0 1.872-1.002l.164-.246A2.25 2.25 0 0 1 16.954 9h4.636M2.41 9a2.25 2.25 0 0 0-.16.832V12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 12V9.832c0-.287-.055-.57-.16-.832M2.41 9a2.25 2.25 0 0 1 .382-.632l3.285-3.832a2.25 2.25 0 0 1 1.708-.786h8.43c.657 0 1.281.287 1.709.786l3.284 3.832c.163.19.291.404.382.632M4.5 20.25h15A2.25 2.25 0 0 0 21.75 18v-2.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 0 0 2.25 2.25Z"
                />
              </svg>

              Orders
            </a>
          </li>
          <li>
            <a href="#" class="link-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"
                />
              </svg>
              Help
            </a>
          </li>
        </ul>
        <div class="search-product">
          <input type="text" placeholder="Search products" />
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
            />
          </svg>
          
        </div>
      </div>
    </header>

    <!-- ══ HERO ══ -->
    <section class="hero">
      <div class="hero-inner">
        <span class="hero-badge">Nigerian army shopping arena</span>
        <h1>Find Everything in <span>Arena Market</span></h1>
        <p>
          Explore thousands of shops selling clothes, shoes, bags and more — all
          in one place.
        </p>
        <div class="hero-buttons">
          <button class="btn-primary">Browse Shops</button>
          <button class="btn-outline">View All Categories</button>
        </div>
      </div>
    </section>

    <!-- ══ CATEGORIES ══ -->
    <section class="section">
      <div class="section-header">
        <h2 class="section-title">Browse Categories</h2>
        <a href="#" class="see-all">See all →</a>
      </div>
      <div class="carousel">
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/shirt.jpg"
            alt="Shirt"
          />
          <div class="image-content">Shirt</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/bags.jpg"
            alt="Bags"
          />
          <div class="image-content">Bags</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/traveelling bag.jpg"
            alt="Travelling Bag"
          />
          <div class="image-content">Travelling Bag</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/office shoe.jpg"
            alt="Men Shoe"
          />
          <div class="image-content">Men Shoe</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/woman shoe.jpg"
            alt="women Shoe"
          />
          <div class="image-content">women Shoe</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/sneakers.jpg"
            alt="Sneakers"
          />
          <div class="image-content">Sneakers</div>
        </div>
           <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/crocs.jpg"
            alt="Crocs"
          />
          <div class="image-content">Crocs</div>
        </div>
           <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/slide.jpg"
            alt="Slide"
          />
          <div class="image-content">Slide</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/office shirt.jpg"
            alt="Office Shirts"
          />
          <div class="image-content">Office Shirts</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/women slide.jpg"
            alt="Women Slide"
          />
          <div class="image-content">Women Slide</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/pant trouser.jpg"
            alt="Pant Trouser"
          />
          <div class="image-content">Pant Trouser</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/jeans.jpg"
            alt="Jeans"
          />
          <div class="image-content">Jeans</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/body cream.jpg"
            alt="Body cream"
          />
          <div class="image-content">Body cream</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/body soap.jpg"
            alt="Body Soap"
          />
          <div class="image-content">Body Soap</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/spray.jpg"
            alt="Body Spray"
          />
          <div class="image-content">Body Spray</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/perfume.jpg"
            alt="Perfume"
          />
          <div class="image-content">Perfume</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/deodorant.jpg"
            alt="Deodorant"
          />
          <div class="image-content">Deodorant</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/fridge.jpg"
            alt="Fridge"
          />
          <div class="image-content">Fridge</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/jbl.jpg"
            alt="JBL"
          />
          <div class="image-content">JBL</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/television.jpg"
            alt="Television"
          />
          <div class="image-content">Television</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/iron.jpg"
            alt="Iron"
          />
          <div class="image-content">Iron</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/standing fan.jpg"
            alt="Standing fan"
          />
          <div class="image-content">Standing fan</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/ceoling fan.jpg"
            alt="Ceiling Fan"
          />
          <div class="image-content">Ceiling Fan</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
      
      </div>
      
    </section>

    <!-- ══ MARKET VENDORS ══ -->
    <section
      class="section"
      style="
        background: #fafafa;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
      "
    >
      <div class="section-header">
        <h2 class="section-title">Market Vendors</h2>
        <a href="#" class="see-all">See all →</a>
      </div>
      <div class="carousel-card">
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
      </div>
    </section>

    <!-- ══ TRENDING ══ -->
    <section class="section">
      <div class="section-header">
        <h2 class="section-title">Trending in the Market</h2>
        <a href="#" class="see-all">See all →</a>
      </div>
      <div class="carousel-market-card">
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
      </div>
    </section>

     <!-- ══ TRENDING ══ -->
    <section class="section">
      <div class="section-header">
        <h2 class="section-title">New in the Market</h2>
        <a href="#" class="see-all">See all →</a>
      </div>
      <div class="carousel-market-card">
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
      </div>
    </section>


    <!-- ══ NEWSLETTER ══ -->
    <div class="newsletter-section">
      <h2>Subscribe to Our Newsletter</h2>
      <p>Get updates on our latest offers and new arrivals</p>
      <div class="newsletter-form">
        <input
          class="newsletter-input"
          type="email"
          placeholder="Enter your email address"
        />
        <button class="newsletter-btn">Subscribe</button>
      </div>
    </div>

    <!-- ══ FOOTER ══ -->
    <footer>
      <div class="footer-grid">
        <div class="footer-col">
          <h4>About Arena</h4>
          <ul>
            <li><a href="#">About us</a></li>
            <li><a href="#">How it works</a></li>
            <li><a href="#">Terms &amp; Conditions</a></li>
            <li><a href="#">Privacy Policy</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Categories</h4>
          <ul>
            <li><a href="#">Fashion</a></li>
            <li><a href="#">Phones &amp; Tablets</a></li>
            <li><a href="#">Groceries</a></li>
            <li><a href="#">Electronics</a></li>
            <li><a href="#">Appliances</a></li>
            <li><a href="#">Other Categories</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Help &amp; Support</h4>
          <ul>
            <li><a href="#">Help Center</a></li>
            <li><a href="#">FAQs</a></li>
            <li><a href="#">Order Tracking</a></li>
            <li><a href="#">Returns &amp; Refunds</a></li>
            <li><a href="#">Shipping Information</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Our Socials</h4>
          <ul>
            <li><a href="#">Instagram</a></li>
            <li><a href="#">TikTok</a></li>
            <li><a href="#">Facebook</a></li>
            <li><a href="#">Twitter</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Contact Us</h4>
          <ul>
            <li><span>support@thearenamarket.com</span></li>
            <li><span>+234 906 925 9369</span></li>
            <li><span>+234 704 524 3303</span></li>
            <li><span>Bolade Bus-Stop, Oshodi, Lagos, Nigeria</span></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2026 Arena Market. All rights reserved.</p>
        <p>Made with care in Oshodi Lagos, Nigeria</p>
      </div>
    </footer>

    <script>
      // ── Hamburger toggle ──
      const hamburger = document.getElementById("hamburger-btn");
      const mobileMenu = document.getElementById("dropdown-menu");

      hamburger.addEventListener("click", function (e) {
        e.stopPropagation();
        mobileMenu.classList.toggle("open");
      });

      document.addEventListener("click", function () {
        mobileMenu.classList.remove("open");
      });

      mobileMenu.addEventListener("click", function (e) {
        e.stopPropagation();
      });

      // ── Generic dropdown toggle (Orders & Help) ──
      function setupDropdown(triggerId, dropdownId) {
        const trigger = document.getElementById(triggerId);
        const dropdown = document.getElementById(dropdownId);
        if (!trigger || !dropdown) return;
        const chevron = trigger.querySelector(".chevron");

        trigger.addEventListener("click", function (e) {
          e.stopPropagation();
          const isOpen = dropdown.classList.contains("open");
          // close all dropdowns first
          document
            .querySelectorAll(".order-container")
            .forEach((d) => d.classList.remove("open"));
          document
            .querySelectorAll(".chevron")
            .forEach((c) => c.classList.remove("open"));
          // toggle clicked one
          if (!isOpen) {
            dropdown.classList.add("open");
            if (chevron) chevron.classList.add("open");
          }
        });
      }

      setupDropdown("orders-chevron", "orders-dropdown");
      setupDropdown("help-chevron", "help-dropdown");

      document.addEventListener("click", function () {
        document
          .querySelectorAll(".order-container")
          .forEach((d) => d.classList.remove("open"));
        document
          .querySelectorAll(".chevron")
          .forEach((c) => c.classList.remove("open"));
      });

      const add = (b,k) =>{return b+k}
      console.log (add(5,6));

      const multiply = (a,b) => a*b;
      console.log (multiply(5,9));

      const fruits = ['red-apple','yelloe-banana', 'green-mellon'];
      fruits.forEach(fruit => console.log(fruit))
    </script>
  </body>
</html>
