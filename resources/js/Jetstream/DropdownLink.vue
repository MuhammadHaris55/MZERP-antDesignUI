<template>
    <div>
        <button
            type="submit"
            class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-400 hover:text-white hover:border-indigo-400 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
            v-if="as == 'button'"
        >
            <slot></slot>
        </button>

        <inertia-link
            :href="href"
            class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
            v-else
        >
            <slot></slot>
        </inertia-link>
    </div>
</template>

<script>
export default {
    props: ["href", "as"],
};
</script>
