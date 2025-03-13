$(document).ready(function(){
  $(Window).on('scroll load',function(){
    if($(Window).scrollTop()>30){
      $('header').addClass('header-active');
    }else{
      $('header').removeClass('header-active');
    }
  })
})

function validate(){
  n=document.getElementById("name").value;
  m=document.getElementById("mail").value;
  p=document.getElementById("phone").value;
  message=document.getElementById("message").value;
  if(!n){
    alert("Please Enter Fill in Your Name!!");
    return false;
  }
  if(!p){
    alert("Please Enter Fill in Your Phone No.!!");
    return false;
  }
  if(!m){
    alert("Please Enter Fill in Your Email!!");
    return false;
  }
  if(!message){
    alert("Please Enter Fill in Your Message!!");
    return false;
  }
  
  return true;
  

}