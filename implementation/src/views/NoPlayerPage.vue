<!--  Ksenija Bulatovic 2019/0730
      Miloš Ćirković 2017/0333  -->

<template>
  <div class="login">
    <Nav/>
    <br/>
    <div class="row">
      <div class="col-6">
        <login-card/>
      </div>
      <div class="col-6">
        <register-card/>
      </div>

    </div>

  </div>
</template>

<script>
import Nav from '@/components/Nav'
import LoginCard from '../components/noUser/LoginCard.vue'
import RegisterCard from '../components/noUser/RegisterCard.vue'

export default {
  name:'Login',
  components: {
    Nav,
    LoginCard,
    RegisterCard
  },
  beforeMount() {
    if(localStorage.UserTypeId == 1)
      this.$router.replace('/player');
    else if(localStorage.UserTypeId == 2)
      this.$router.replace('/professor');
    else if(localStorage.UserTypeId == 3)
      this.$router.replace('/admin');
  }
}
</script>