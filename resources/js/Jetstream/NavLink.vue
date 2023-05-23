<template>
  <inertia-link :href="href" :class="classes">
    <slot></slot>
  </inertia-link>
</template>

<script>
export default {
  props: ["href", "active"],

  computed: {
    classes() {
      return this.active
        ? "inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-md font-medium font-bold leading-5 text-black focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
        : "inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-700 hover:text-black hover:border-indigo-400 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out";
    },
  },
};
</script>
