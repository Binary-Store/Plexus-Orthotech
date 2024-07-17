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
    html += `<a href="./product.html?category=${category.name}&subcategory=" class="category_link">${category.name}</a></h3><ul>`;
    category.subcategories.forEach((subcategory, index) => {
      if (index < 7) html += `<li><a href="./product.html?category=${category.name}&subcategory=${subcategory.name}" class="subcategory_link">${subcategory.name}</a></li>`;
      if (index == 7) html += `<li><a href="./product.html?category=${category.name}&subcategory=" class="subcategory_link">View More</a></li>`;
    });
    html += `</ul>`;
    html += `</div>`;
  });
  mega_menu_row.innerHTML = html;

}
