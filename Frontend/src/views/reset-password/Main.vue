<template>
  <div>
    <DarkModeSwitcher />
    <div class="container sm:px-10">
      <div class="block xl:grid grid-cols-2 gap-4">
        <!-- BEGIN: Login Info -->
        <div class="hidden xl:flex flex-col min-h-screen">
          <a href="" class="-intro-x flex items-center pt-5">
            <img
              alt=""
              class="w-6"
              :src="this.wiki_settings.logo"
            />
            <span class="text-white text-lg ml-3">
              {{ this.wiki_settings.name }}
            </span>
          </a>
          <div class="my-auto">
            <img
              alt=""
              class="-intro-x w-1/2 -mt-16"
              :src="require(`@/assets/images/illustration.svg`)"
            />
            <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">
              A few more clicks to <br />
              reset your account password.
            </div>
            <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-gray-500">
              Free of charge and opensource developed by KRB-Development
            </div>
          </div>
        </div>
        <!-- END: Login Info -->
        <!-- BEGIN: Login Form -->
        <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
          <div
            class="my-auto mx-auto xl:ml-20 bg-white dark:bg-dark-1 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto"
          >
            <h2
              class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left"
            >
              Reset Password
            </h2>
            <div class="intro-x mt-2 text-gray-500 xl:hidden text-center">
              A few more click to reset your password.
            </div>
            <form @submit.prevent="handleSubmit">
              <div class="intro-x mt-8">
                <input
                  type="email"
                  :class="'intro-x login__input form-control py-3 px-4 border-gray-300 block mt-4' + (this.validation_error?.email != null ? ' border-theme-6' : '')"
                  placeholder="E-Mail"
                  v-model="email"
                />

                <div class="text-theme-6 mt-2 mb-4" v-if='this.validation_error?.email != null'>
                  {{ this.validation_error?.email[0] }}
                </div>

                <input
                  type="password"
                  :class="'intro-x login__input form-control py-3 px-4 border-gray-300 block mt-4' + (this.validation_error?.password != null ? ' border-theme-6' : '')"
                  placeholder="Password"
                  v-model="password"
                />

                <div class="text-theme-6 mt-2 mb-4" v-if='this.validation_error?.password != null'>
                  {{ this.validation_error?.password[0] }}
                </div>

                <input
                 type="password"
                  :class="'intro-x login__input form-control py-3 px-4 border-gray-300 block mt-4' + (this.validation_error?.password_confirmation != null ? ' border-theme-6' : '')"
                  placeholder="Password"
                  v-model="password_confirmation"
                />

                <div class="text-theme-6 mt-2 mb-4" v-if='this.validation_error?.password_confirmation != null'>
                  {{ this.validation_error?.password_confirmation[0] }}
                </div>
              </div>
              <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                <button
                  class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top"
                  type="submit"
                >
                  Reset
                </button>
              </div>
              <div
                class="intro-x mt-10 xl:mt-24 text-gray-700 dark:text-gray-600 text-center xl:text-left"
              >
                By resetting password, you agree to our <br />
                <a class="text-theme-1 dark:text-theme-10" href="">
                  Terms and Conditions
                </a>
                &
                <a class="text-theme-1 dark:text-theme-10" href="">
                  Privacy Policy
                </a>
              </div>
            </form>
          </div>
        </div>
        <!-- END: Login Form -->
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, onMounted } from 'vue'
import DarkModeSwitcher from '@/components/dark-mode-switcher/Main.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
const toast = useToast()

export default defineComponent({
  components: {
    DarkModeSwitcher
  },
  setup() {
    onMounted(() => {
      cash('body')
        .removeClass('main')
        .removeClass('error-page')
        .addClass('login')
    })
  },
  data() {
    return {
      password: '',
      password_confirmation: '',
      email: '',
      wiki_settings: {
        name: process.env.VUE_APP_NAME,
        logo: process.env.VUE_APP_LOGO
      },
      validation_error: {}
    }
  },
  methods: {
    handleSubmit(e) {
      e.preventDefault()
      this.validation_error = {}

      const loader = this.$loading.show()

      axios.post('auth/password/reset', {
        token: this.$route.query.token,
        email: this.email,
        password: this.password,
        password_confirmation: this.password_confirmation
      })
        .then(response => {
          toast.success(response.data.message)
          loader.hide()
          this.$router.push({ name: 'login' })
        })
        .catch(error => {
          console.log(error.response)
          this.validation_error = error.response.data.data.errors
          toast.error(error.response.data.message)
          loader.hide()
        })
    },
    isMail: function () {
      const reg = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/
      return reg.test(this.email)
    }
  }
})
</script>
