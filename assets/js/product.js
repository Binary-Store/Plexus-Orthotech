function fetchCategory() {
  $.ajax({
    url: './admin/includes/category.php', // Your API endpoint
    method: 'GET',
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        console.log(response.categories)
        renderCategory(response.categories);
      } else {
        console.error('Error fetching categories:', response.error);
      }
    },
    error: function (xhr, status, error) {
      console.error('AJAX error:', error);
    }
  });
}
fetchCategory();

function renderCategory(categoryData) {
  //    i want to fill this data to mega-menu row
  const mega_menu_row = document.getElementById('mega_menu_row');
  let html = '';
  categoryData.forEach((category) => {
    html += `<div class="mega-menu-column">
                              <h3 class="category_heading">`
    html += `<a href="./product.html?category=${category.name}&subcategory=" class="category_link">${category.name} </a> <span>v</span> </h3><ul>`;
    category.subcategories.forEach((subcategory, index) => {
      if (index < 7) html += `<li><a href="./product.html?category=${category.name}&subcategory=${subcategory.name}" class="subcategory_link">${subcategory.name}</a></li>`;
      if (index == 7) html += `<li><a href="./product.html?category=${category.name}&subcategory=" class="subcategory_link">View More</a></li>`;
    });
    html += `</ul>`;
    html += `</div>`;
  });
  mega_menu_row.innerHTML = html;

}

document.addEventListener('DOMContentLoaded', function () {
  var productLink = document.getElementById('product-link');
  var productMenu = document.getElementById('product-menu');
  
  productLink.addEventListener('click', function (e) {
      e.preventDefault();
      if (window.innerWidth <= 990) {
          document.getElementById('product-link-arrow').classList.toggle('active')
          productMenu.classList.toggle('active');
      }
  });

  var categoryHeadings = document.querySelectorAll('.mega-menu-column h3');
  categoryHeadings.forEach(function (heading) {
      heading.addEventListener('click', function (e) {
          e.preventDefault();
          this.classList.toggle("open");
          var ul = this.nextElementSibling;
          if (ul && ul.tagName === 'UL') {
              ul.classList.toggle('active');
          }
      });
  });
});


