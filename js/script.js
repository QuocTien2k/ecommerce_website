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

let mainImage = document.querySelector('.quick-view .box .row .image-container .main-image img');
let subImages = document.querySelectorAll('.quick-view .box .row .image-container .sub-image img');

subImages.forEach(images =>{
   images.onclick = () =>{
      src = images.getAttribute('src');
      mainImage.src = src;
   }
});

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

const modal = document.getElementById("searchModal");
   const btn = document.getElementById("openModal");

   // Mở modal khi nhấn vào nút
   btn.onclick = function() {
      modal.style.display = "block";
   }

   // Đóng modal khi nhấn ra ngoài modal
   window.onclick = function(event) {
      if (event.target == modal) {
         modal.style.display = "none";
      }
   }


