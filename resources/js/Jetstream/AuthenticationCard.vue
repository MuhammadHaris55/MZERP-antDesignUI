<template>
  <div
    class="
      relative
      min-h-screen
      flex flex-col
      sm:justify-center
      items-center
      pt-6
      sm:pt-0
      bg-gray-100
    "
  >
    <!-- <img
      class="w-screen h-screen"
      src="img/ledger2.jpg"
      alt="Welcome to SA.pk"
    /> -->
    <!-- /home/junaid/repo/laravel/mzerp/public/img/ledger2.jpg -->

    <div>
      <slot name="logo" />
    </div>
    <div
      class="
        w-full
        sm:max-w-md
        mt-6
        px-6
        py-4
        bg-white
        shadow-md
        overflow-hidden
        sm:rounded-lg
      "
    >
      <slot />
    </div>
  </div>
</template>
