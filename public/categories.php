<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>All Categories – Arena Market</title>
  <style>
    :root {
      --orange: #d47719;
      --brown: #3E2C23;
      --orange-light: #f5b474;
      --gray: #f5f5f5;
      --border: #e8e8e8;
      --text-muted: #777;
      --nav-h: 64px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f7f7f7; color: #222; }
    a { text-decoration: none; color: inherit; }
    button { border: none; background: none; cursor: pointer; font-family: inherit; }

    /* HEADER */
    header { position: sticky; top: 0; z-index: 200; background: #fff; border-bottom: 1px solid var(--border); }
    nav { height: var(--nav-h); display: flex; align-items: center; justify-content: space-between; padding: 0 20px; gap: 16px; }
    .logo-search { display: flex; align-items: center; gap: 12px; }
    .logo { height: 32px; width: auto; }
    .search-box { display: flex; align-items: center; border: 1.5px solid var(--border); border-radius: 8px; padding: 6px 12px; gap: 8px; transition: border-color 0.2s; }
    .search-box:focus-within { border-color: var(--orange); }
    .search-box input { border: none; font-size: 13px; width: 220px; background: transparent; outline: none; }
    .search-box input::placeholder { color: #aaa; }
    .search-icon { width: 16px; height: 16px; color: #aaa; flex-shrink: 0; }
    .nav-icons { display: flex; align-items: center; gap: 4px; }
    .nav-icons a { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; color: #444; transition: background 0.15s, color 0.15s; }
    .nav-icons a:hover { background: var(--gray); color: var(--orange); }
    .nav-icon { width: 20px; height: 20px; }

    /* BREADCRUMB */
    .breadcrumb { padding: 14px 20px; font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; background: #fff; border-bottom: 1px solid var(--border); }
    .breadcrumb a { color: var(--orange); }
    .breadcrumb a:hover { text-decoration: underline; }
    .breadcrumb svg { width: 12px; height: 12px; stroke: #bbb; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    /* LAYOUT */
    .page-wrapper { max-width: 1200px; margin: 0 auto; padding: 24px 20px 60px; }

    .page-title { font-size: 1.3rem; font-weight: 700; color: var(--brown); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
    .page-title::before { content: ''; display: block; width: 4px; height: 22px; background: var(--orange); border-radius: 2px; }

    /* DEPARTMENT */
    .department { margin-bottom: 36px; }
    .dept-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: var(--brown); border-radius: 12px 12px 0 0; }
    .dept-title { display: flex; align-items: center; gap: 10px; color: #fff; font-size: 0.95rem; font-weight: 700; }
    .dept-title svg { width: 18px; height: 18px; stroke: var(--orange-light); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .dept-see-all { font-size: 11px; font-weight: 600; color: var(--orange-light); }
    .dept-see-all:hover { text-decoration: underline; }

    /* GRID */
    .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 1px; background: var(--border); border: 1px solid var(--border); border-top: none; border-radius: 0 0 12px 12px; overflow: hidden; }
    .cat-item { background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 18px 10px 14px; gap: 10px; cursor: pointer; transition: background 0.15s; text-align: center; }
    .cat-item:hover { background: #fff8f2; }
    .cat-item:hover .cat-name { color: var(--orange); }
    .cat-item:hover .cat-img, .cat-item:hover .cat-icon-placeholder { border-color: var(--orange); transform: scale(1.05); }
    .cat-img { width: 72px; height: 72px; object-fit: cover; border-radius: 10px; border: 2px solid transparent; transition: border-color 0.2s, transform 0.2s; background: var(--gray); }
    .cat-icon-placeholder { width: 72px; height: 72px; border-radius: 10px; background: linear-gradient(135deg, #fff0e0, #ffe0b2); display: flex; align-items: center; justify-content: center; border: 2px solid transparent; transition: border-color 0.2s, transform 0.2s; flex-shrink: 0; }
    .cat-icon-placeholder svg { width: 30px; height: 30px; stroke: var(--orange); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
    .cat-name { font-size: 11.5px; font-weight: 600; color: #333; line-height: 1.3; transition: color 0.15s; }

    /* NEWSLETTER */
    .newsletter-section { background: linear-gradient(135deg, var(--brown) 0%, #5c3d2e 100%); padding: 48px 20px; text-align: center; margin-top: 40px; border-radius: 16px; }
    .newsletter-section h2 { color: #fff; font-size: 1.3rem; margin-bottom: 8px; }
    .newsletter-section p { color: rgba(255,255,255,0.7); font-size: 13px; margin-bottom: 24px; }
    .newsletter-form { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; max-width: 460px; margin: 0 auto; }
    .newsletter-input { flex: 1; min-width: 200px; height: 42px; border: 1.5px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 0 16px; background: rgba(255,255,255,0.08); color: #fff; font-size: 13px; outline: none; }
    .newsletter-input::placeholder { color: rgba(255,255,255,0.4); }
    .newsletter-input:focus { border-color: var(--orange-light); }
    .newsletter-btn { height: 42px; padding: 0 24px; background: var(--orange); color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .newsletter-btn:hover { background: #b8660f; }

    /* FOOTER */
    footer { background: #1a1109; color: rgba(255,255,255,0.75); padding: 48px 20px 24px; margin-top: 40px; }
    .footer-grid { display: flex; flex-wrap: wrap; gap: 32px; max-width: 1200px; margin: 0 auto 40px; }
    .footer-col { flex: 1 1 150px; }
    .footer-col h4 { color: #fff; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
    .footer-col ul li { margin-bottom: 9px; }
    .footer-col ul li a { font-size: 12px; color: rgba(255,255,255,0.6); transition: color 0.2s; }
    .footer-col ul li a:hover { color: var(--orange-light); }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; max-width: 1200px; margin: 0 auto; font-size: 12px; color: rgba(255,255,255,0.35); }

    @media (min-width: 600px) {
      .cat-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
      .cat-img, .cat-icon-placeholder { width: 80px; height: 80px; }
    }
    @media (min-width: 1100px) {
      nav { padding: 0 40px; }
      .page-wrapper { padding: 28px 40px 60px; }
    }
  </style>
</head>
<body>

<!-- HEADER -->
<header>
  <nav>
    <div class="logo-search">
      <a href="home.html"><img src="./images/logo.jpg" class="logo" alt="Arena Market" /></a>
      <div class="search-box">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        <input type="text" placeholder="Search products or shops" />
      </div>
    </div>
    <div class="nav-icons">
      <a href="" title="Wishlist"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg></a>
      <a href="" title="My Account"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></a>
      <a href="" title="Cart"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg></a>
    </div>
  </nav>
</header>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <a href="home.html">Home</a>
  <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
  <span>All Categories</span>
</div>

<!-- PAGE -->
<div class="page-wrapper">
  <h1 class="page-title">All Categories</h1>

  <!-- FASHION & CLOTHING -->
  <div class="department">
    <div class="dept-header">
      <div class="dept-title">
        <svg viewBox="0 0 24 24"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/></svg>
        Fashion &amp; Clothing
      </div>
      <a href="#" class="dept-see-all">See all →</a>
    </div>
    <div class="cat-grid">
      <a href="#" class="cat-item"><img class="cat-img" src="./images/shirt.jpg" alt="Shirts"/><span class="cat-name">Shirts</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/office shirt.jpg" alt="Office Shirts"/><span class="cat-name">Office Shirts</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/pant trouser.jpg" alt="Pant Trousers"/><span class="cat-name">Pant Trousers</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/jeans.jpg" alt="Jeans"/><span class="cat-name">Jeans</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/></svg></div><span class="cat-name">Dresses</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/></svg></div><span class="cat-name">Suits</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/></svg></div><span class="cat-name">Traditional</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/></svg></div><span class="cat-name">Kids Wear</span></a>
    </div>
  </div>

  <!-- SHOES & FOOTWEAR -->
  <div class="department">
    <div class="dept-header">
      <div class="dept-title">
        <svg viewBox="0 0 24 24"><path d="M3 12c0 0 2-4 9-4s9 4 9 4-2 4-9 4-9-4-9-4z"/><circle cx="12" cy="12" r="2"/></svg>
        Shoes &amp; Footwear
      </div>
      <a href="#" class="dept-see-all">See all →</a>
    </div>
    <div class="cat-grid">
      <a href="#" class="cat-item"><img class="cat-img" src="./images/office shoe.jpg" alt="Men Shoes"/><span class="cat-name">Men Shoes</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/woman shoe.jpg" alt="Women Shoes"/><span class="cat-name">Women Shoes</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/sneakers.jpg" alt="Sneakers"/><span class="cat-name">Sneakers</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/crocs.jpg" alt="Crocs"/><span class="cat-name">Crocs</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/slide.jpg" alt="Slides"/><span class="cat-name">Slides</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/women slide.jpg" alt="Women Slides"/><span class="cat-name">Women Slides</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></div><span class="cat-name">Sandals</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></div><span class="cat-name">Kids Shoes</span></a>
    </div>
  </div>

  <!-- BAGS & ACCESSORIES -->
  <div class="department">
    <div class="dept-header">
      <div class="dept-title">
        <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Bags &amp; Accessories
      </div>
      <a href="#" class="dept-see-all">See all →</a>
    </div>
    <div class="cat-grid">
      <a href="#" class="cat-item"><img class="cat-img" src="./images/bags.jpg" alt="Bags"/><span class="cat-name">Bags</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/traveelling bag.jpg" alt="Travelling Bags"/><span class="cat-name">Travelling Bags</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div><span class="cat-name">Handbags</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div><span class="cat-name">Backpacks</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div><span class="cat-name">Wallets</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div><span class="cat-name">Belts</span></a>
    </div>
  </div>

  <!-- HEALTH & BEAUTY -->
  <div class="department">
    <div class="dept-header">
      <div class="dept-title">
        <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        Health &amp; Beauty
      </div>
      <a href="#" class="dept-see-all">See all →</a>
    </div>
    <div class="cat-grid">
      <a href="#" class="cat-item"><img class="cat-img" src="./images/body cream.jpg" alt="Body Cream"/><span class="cat-name">Body Cream</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/body soap.jpg" alt="Body Soap"/><span class="cat-name">Body Soap</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/spray.jpg" alt="Body Spray"/><span class="cat-name">Body Spray</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/perfume.jpg" alt="Perfume"/><span class="cat-name">Perfume</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/deodorant.jpg" alt="Deodorant"/><span class="cat-name">Deodorant</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div><span class="cat-name">Hair Care</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div><span class="cat-name">Skincare</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div><span class="cat-name">Makeup</span></a>
    </div>
  </div>

  <!-- ELECTRONICS & APPLIANCES -->
  <div class="department">
    <div class="dept-header">
      <div class="dept-title">
        <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        Electronics &amp; Appliances
      </div>
      <a href="#" class="dept-see-all">See all →</a>
    </div>
    <div class="cat-grid">
      <a href="#" class="cat-item"><img class="cat-img" src="./images/television.jpg" alt="Television"/><span class="cat-name">Television</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/fridge.jpg" alt="Fridge"/><span class="cat-name">Fridge</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/jbl.jpg" alt="Speakers"/><span class="cat-name">Speakers</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/iron.jpg" alt="Iron"/><span class="cat-name">Iron</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/standing fan.jpg" alt="Standing Fan"/><span class="cat-name">Standing Fan</span></a>
      <a href="#" class="cat-item"><img class="cat-img" src="./images/ceoling fan.jpg" alt="Ceiling Fan"/><span class="cat-name">Ceiling Fan</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg></div><span class="cat-name">Phones</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div><span class="cat-name">Laptops</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg></div><span class="cat-name">Blender</span></a>
      <a href="#" class="cat-item"><div class="cat-icon-placeholder"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 2v5M8 2v5M2 12h20"/></svg></div><span class="cat-name">Microwave</span></a>
    </div>
  </div>

  <!-- NEWSLETTER -->
  <div class="newsletter-section">
    <h2>Stay in the Loop</h2>
    <p>Get the latest deals and new arrivals straight to your inbox.</p>
    <div class="newsletter-form">
      <input class="newsletter-input" type="email" placeholder="Enter your email address" />
      <button class="newsletter-btn">Subscribe</button>
    </div>
  </div>

</div>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div class="footer-col">
      <h4>Arena Market</h4>
      <ul><li><a href="#">About Us</a></li><li><a href="#">Careers</a></li><li><a href="#">Press</a></li><li><a href="#">Blog</a></li></ul>
    </div>
    <div class="footer-col">
      <h4>Help</h4>
      <ul><li><a href="#">Help Centre</a></li><li><a href="#">Track an Order</a></li><li><a href="#">Returns &amp; Refunds</a></li><li><a href="#">Contact Us</a></li></ul>
    </div>
    <div class="footer-col">
      <h4>Sell on Arena</h4>
      <ul><li><a href="#">Become a Vendor</a></li><li><a href="#">Vendor Portal</a></li><li><a href="#">Seller Guidelines</a></li></ul>
    </div>
    <div class="footer-col">
      <h4>Policies</h4>
      <ul><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Use</a></li><li><a href="#">Cookie Policy</a></li></ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2026 Arena Market. All rights reserved.</p>
    <p>Nigerian Army Shopping Arena</p>
  </div>
</footer>

</body>
</html>
