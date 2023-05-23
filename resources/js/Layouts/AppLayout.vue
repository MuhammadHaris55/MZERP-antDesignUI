
<template>
  <Layout style="min-height: 100vh">
    <LayoutSider v-model:collapsed="collapsed" collapsible>
      <div class="logo flex-shrink-0 flex items-center">
        <inertia-link :href="route('dashboard')">
          <jet-application-mark class="block h-9 w-auto" />
        </inertia-link>
      </div>
      <!-- <div class="logo" /> -->
      <Menu theme="dark" v-model:selectedKeys="selectedKeys" mode="inline">
        <MenuItem key="1">
          <pie-chart-outlined />
          <jet-nav-link
            :href="route('companies')"
            :active="route().current('companies')"
          >
            Companies
          </jet-nav-link>

          <!-- <span>Option 1</span> -->
        </MenuItem>
        <MenuItem v-if="this.$page.props.co_id" key="2">
          <desktop-outlined />
          <jet-nav-link
            :href="route('years')"
            :active="route().current('years')"
          >
            Years
          </jet-nav-link>
        </MenuItem>

        <MenuItem  v-if="this.$page.props.co_id && this.$page.props.yr_id"
                 key="3">
          <desktop-outlined />
          <jet-nav-link
           :href="route('accountgroups')"
            :active="route().current('accountgroups')"
          >
            Account Groups
          </jet-nav-link>

          <!-- <span>Option 2</span> -->
        </MenuItem>

        <MenuItem v-if="this.$page.props.co_id && this.$page.props.yr_id"
                key="4">
          <desktop-outlined />
          <jet-nav-link
            :href="route('accounts')"
            :active="route().current('accounts')"
          >
            Accounts
          </jet-nav-link>

          <!-- <span>Option 2</span> -->
        </MenuItem>

        <MenuItem  v-if="this.$page.props.co_id && this.$page.props.yr_id"
                key="5">
          <desktop-outlined />
          <jet-nav-link
            :href="route('documenttypes')"
            :active="route().current('documenttypes')"
          >
            Vouchers
          </jet-nav-link>

          <!-- <span>Option 2</span> -->
        </MenuItem>

        <MenuItem   v-if="this.$page.props.co_id && this.$page.props.yr_id"
               key="6">
          <desktop-outlined />
          <jet-nav-link
            :href="route('documents')"
            :active="route().current('documents')"
          >
            Transactions
          </jet-nav-link>

          <!-- <span>Option 2</span> -->
        </MenuItem>

        <MenuItem   v-if="this.$page.props.co_id && this.$page.props.yr_id"
              key="7">
          <desktop-outlined />
          <jet-nav-link
            :href="route('reports')"
            :active="route().current('reports')"
          >
            Reports
          </jet-nav-link>

          <!-- <span>Option 2</span> -->
        </MenuItem>
        <MenuItem v-if="this.$page.props.co_id && this.$page.props.yr_id"
                key="8">
          <desktop-outlined />
          <jet-nav-link
            :href="route('ledgers')"
            :active="route().current('ledgers')"
          >
            Ledger
          </jet-nav-link>

          <!-- <span>Option 2</span> -->
        </MenuItem>
        <MenuItem key="9">
          <jet-nav-link
            :href="route('profile.show')"
            :active="route().current('profile.show')"
          >
            Profile
          </jet-nav-link>
        </MenuItem>
        <MenuItem key="10">
          <!-- Authentication -->
          <form @submit.prevent="logout">
            <jet-dropdown-link as="button"> Log Out </jet-dropdown-link>
          </form>
        </MenuItem>

        <!-- <SubMenu key="sub1">
          <template #title>
            <span>
              <user-outlined />
              <span>User</span>
            </span>
          </template>
          <MenuItem key="3">Tom</MenuItem>
          <MenuItem key="4">Bill</MenuItem>
          <MenuItem key="5">Alex</MenuItem>
        </SubMenu> -->
        <!-- <SubMenu key="sub2">
          <template #title>
            <span>
              <team-outlined />
              <span>Team</span>
            </span>
          </template>
          <MenuItem key="6">Team 1</MenuItem>
          <MenuItem key="8">Team 2</MenuItem>
        </SubMenu> -->
      </Menu>
    </LayoutSider>
    <Layout>
      <!-- Page Heading -->
      <LayoutHeader class="bg-gray-800 shadow" v-if="$slots.header">
        <div class="max-w-7xl mx-auto">
          <slot name="header"></slot>
        </div>
      </LayoutHeader>
      <FlashMessage />

      <LayoutContent style="margin: 0 16px">
        <Breadcrumb style="margin: 16px 0">
          <BreadcrumbItem></BreadcrumbItem>
          <!-- <BreadcrumbItem>Bill</BreadcrumbItem> -->
        </Breadcrumb>
        <div
          :style="{ padding: '24px', background: '#fff', minHeight: '360px' }"
        >
          <!-- Page Content -->
          <main>
            <slot></slot>
          </main>
          <!-- ---- -->
        </div>
      </LayoutContent>
      <LayoutFooter style="text-align: center">
        Copyright © {{ currentYear }}, SA-accounting.
      </LayoutFooter>
    </Layout>
  </Layout>
</template>

<script>
import JetApplicationMark from "@/Jetstream/ApplicationMark";
import JetBanner from "@/Jetstream/Banner";
import JetDropdown from "@/Jetstream/Dropdown";
import JetDropdownLink from "@/Jetstream/DropdownLink";
import JetNavLink from "@/Jetstream/NavLink";
import JetResponsiveNavLink from "@/Jetstream/ResponsiveNavLink";
import FlashMessage from "@/Layouts/FlashMessage";
import "ant-design-vue/dist/antd.css";
import {
  Layout,
  Menu,
  LayoutFooter,
  LayoutHeader,
  MenuItem,
  Breadcrumb,
  BreadcrumbItem,
  SubMenu,
  LayoutSider,
  LayoutContent,
} from "ant-design-vue";

export default {
  components: {
    JetApplicationMark,
    JetBanner,
    JetDropdown,
    JetDropdownLink,
    JetNavLink,
    JetResponsiveNavLink,
    Layout,
    Breadcrumb,
    Menu,
    LayoutSider,
    MenuItem,
    SubMenu,
    BreadcrumbItem,
    LayoutHeader,
    LayoutContent,
    LayoutFooter,
    FlashMessage,
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
  },

  data() {
    return {
      showingNavigationDropdown: false,
    };
  },

  methods: {
    switchToTeam(team) {
      this.$inertia.put(
        route("current-team.update"),
        {
          team_id: team.id,
        },
        {
          preserveState: false,
        }
      );
    },

    logout() {
      this.$inertia.post(route("logout"));
    },
  },
};
</script>

<style>
.ant-layout-sider-children .logo {
  height: 38px;
  margin: 16px;
  background: rgba(255, 255, 255, 0.3);
}

.site-layout .site-layout-background {
  background: #fff;
}
[data-theme="dark"] .site-layout .site-layout-background {
  background: #141414;
}
</style>
