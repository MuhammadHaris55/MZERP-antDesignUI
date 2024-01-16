<template>
    <div class="fixed flex items-top bg-gray-700 dark:bg-gray-900 pt-0 inset-0">
        <div class="flex flex-row items-center justify-between fixed px-6 py-1">
            <div class="">
                <!-- <inertia-link
          :href="route('login')"
          class="text-md text-white ml-5 hover:text-blue-300"
        >
          Confirmation.pk
        </inertia-link> -->
            </div>
            <div
                class="fixed inset-y-0 right-0 mt-4 mr-14 py-5"
                v-if="canLogin"
            >
                <inertia-link
                    v-if="$page.props.user"
                    href="/dashboard"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150 ml-4"
                >
                    Dashboard
                </inertia-link>

                <template v-else>
                    <!-- hover:text-blue-300 -->
                    <inertia-link
                        :href="route('login')"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150 ml-4"
                    >
                        Login
                    </inertia-link>

                    <!-- class="text-sm text-white ml-5 hover:text-blue-300" -->
                </template>
            </div>
        </div>
        <!-- End if -->
        <div class="">
            <div class="flex w-screen h-screen z-10">
                <!-- src="img/wallpaper3.jpg" -->
                <!-- wallpapr -->
                <img
                    class="px-2 w-full object-cover"
                    src="img/ledger.jpg"
                    alt="Welcome to SA.pk"
                />
                <div
                    class="absolute z-20 invisible md:visible w-50% my-2 text-left"
                >
                    <inertia-link :href="route('login')">
                        <div
                            class="absolute z-20 invisible md:visible top-18 md:left-10 lg:left-35"
                        >
                            <svg
                                id="svg8"
                                width="178mm"
                                height="55mm"
                                version="1.1"
                                viewBox="0 0 190 33.611"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <g
                                    id="layer1"
                                    transform="translate(-8.6366 -101.9)"
                                >
                                    <rect
                                        id="rect10"
                                        x="9.6366"
                                        y="102.9"
                                        width="23.397"
                                        height="29.612"
                                        fill="none"
                                        stroke="#000"
                                        stroke-width="2"
                                    />
                                    <text
                                        id="text837"
                                        x="15.38962"
                                        y="130.41719"
                                        font-family="'Matura MT Script Capitals'"
                                        font-size="24.976px"
                                        fill="#000"
                                        stroke="#000"
                                        stroke-width="1"
                                        style="line-height: 1.25"
                                        xml:space="preserve"
                                    >
                                        <tspan
                                            id="tspan835"
                                            x="15.38962"
                                            y="130.41719"
                                            font-family="sans-serif"
                                            font-size="24.976px"
                                            stroke-width=".3"
                                        >
                                            S
                                        </tspan>
                                    </text>
                                    <rect
                                        id="rect10-5"
                                        x="33.034"
                                        y="102.9"
                                        width="23.397"
                                        height="29.612"
                                        fill="none"
                                        stroke="#000"
                                        stroke-width="2"
                                    />
                                    <text
                                        id="text837-2"
                                        x="34.55349"
                                        y="130.41719"
                                        font-family="'Matura MT Script Capitals'"
                                        font-size="24.976px"
                                        fill="#000"
                                        stroke="#000"
                                        stroke-width="1"
                                        style="line-height: 1.25"
                                        xml:space="preserve"
                                    >
                                        <tspan
                                            id="tspan835-5"
                                            x="34.55349"
                                            y="130.41719"
                                            font-family="sans-serif"
                                            font-size="24.976px"
                                            stroke-width=".3"
                                        >
                                            A
                                        </tspan>
                                    </text>
                                    <text
                                        id="text837-2-1"
                                        x="59.926598"
                                        y="130.34967"
                                        font-family="'Matura MT Script Capitals'"
                                        font-size="24.976px"
                                        fill="#000"
                                        stroke="#000"
                                        stroke-width="1"
                                        style="line-height: 1.25"
                                        xml:space="preserve"
                                    >
                                        <tspan
                                            id="tspan835-5-6"
                                            x="59.926598"
                                            y="130.34967"
                                            font-family="sans-serif"
                                            font-size="24.976px"
                                            stroke-width=".3"
                                        >
                                            accounting
                                        </tspan>
                                    </text>
                                </g>
                            </svg>
                        </div>
                    </inertia-link>

                    <!-- <p class="ml-8 text-md font-bold text-white">
            Is a online platform that facilitates
          </p>
          <p class="ml-20 text-md font-bold text-white">
            balance confirmation.
          </p> -->
                </div>
                <!-- backdrop-filter: blur(20px) ; -->
                <div
                    style="text-shadow: #1f2937 2px 2px 5px"
                    class="absolute z-20 invisible md:visible bottom-16 right-10"
                >
                    <p
                        class="text-black text-center font-sans font-bold text-2xl"
                    >
                        Powered by
                    </p>
                    <p class="font-bold text-md text-center text-black">
                        Digital Solution Department
                    </p>
                    <p class="font-bold text-md text-black">
                        Muniff Ziauddin & Co. Chartered Accountants
                    </p>
                </div>

                <!-- backdrop-filter: -->
                <!-- blur(30px); -->
                <!-- <div
          class="absolute z-20 invisible md:visible bottom-16 left-10"
          style="text-shadow: 4px 4px 16px #0000ff"
        >
          <p class="text-white text-left font-sans font-bold text-2xl">
            Benefits
          </p>
          <p class="font-bold text-md mt-2 text-left text-white">
            1. Save cost Eliminating extra
          </p>
          <p class="font-bold ml-4 text-md text-left text-white">
            stationery expenses associated<br />
          </p>
          <p class="font-bold ml-4 text-md text-left text-white">
            with conventional method
          </p>
          <p class="font-bold text-md text-left text-white">
            2. Save time<br />
            3. Secure
          </p>
        </div> -->
            </div>
        </div>

        <!--picture end -->
        <div
            class="flex flex-row bottom-px items-center fixed w-full justify-center px-6 py-1"
        >
            <div class="">
                <p
                    style="text-shadow: #1f2937 2px 2px 5px"
                    class="text-sm font-bold bg-blend-screen text-black ml-5"
                >
                    Copyright © 2022, SA-accounting.
                </p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        canLogin: Boolean,
        canRegister: Boolean,
    },
};
</script>
