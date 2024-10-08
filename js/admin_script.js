let navbar = document.querySelector('.header .flex .navbar');
let profile = document.querySelector('.header .flex .profile');

document.querySelector('#menu-btn').onclick = () =>{
   navbar.classList.toggle('active');
   profile.classList.remove('active');
}

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
   navbar.classList.remove('active');
}

window.onscroll = () =>{
   navbar.classList.remove('active');
   profile.classList.remove('active');
}

let mainImage = document.querySelector('.update-product .image-container .main-image img');
// console.log(mainImage);
let subImages = document.querySelectorAll('.update-product .image-container .sub-image img');
// console.log(subImages);

subImages.forEach(image =>{
   // console.log(image);
   // console.log(src= image.src);
   // console.log(mainImage.src = src);
   image.onclick = () =>{
      src = image.getAttribute('src');
      mainImage.src = src;
   }
})

document.addEventListener('DOMContentLoaded', () => {
   const messages = document.querySelectorAll('.message');
   let topOffset = 12; // Bắt đầu từ 10%

   // Hàm để ẩn thông báo với hiệu ứng slideOut
   function hideMessage(message) {
       message.style.animation = 'slideOut 1.2s ease forwards'; // Thêm animation slideOut
       setTimeout(() => {
           message.remove(); // Xóa thông báo sau khi animation kết thúc
       }, 1200); // Thời gian chờ phải bằng thời gian của animation
   }

   // Lặp qua từng thông báo và đặt thời gian để ẩn nó sau 5 giây
   messages.forEach((message, index) => {
       // Điều chỉnh top để các thông báo không chồng lên nhau
       message.style.top = `${topOffset}%`;
       topOffset += 10; // Tăng giá trị top cho thông báo tiếp theo

       setTimeout(() => {
           hideMessage(message);
       }, 4500);
   });
});

document.addEventListener("DOMContentLoaded", function() {
   // Lưu vị trí cuộn khi người dùng nhấn vào các nút phân trang
   document.querySelectorAll('.pagination-link').forEach(function(link) {
      link.addEventListener('click', function() {
         localStorage.setItem('scrollPosition', window.scrollY);
      });
   });
});

document.addEventListener("DOMContentLoaded", function() {
   // Khôi phục vị trí cuộn từ localStorage
   if (localStorage.getItem('scrollPosition') !== null) {
      window.scrollTo(0, localStorage.getItem('scrollPosition'));
      localStorage.removeItem('scrollPosition'); // Xóa sau khi sử dụng
   }
});
