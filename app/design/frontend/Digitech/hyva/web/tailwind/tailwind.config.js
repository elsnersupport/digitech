const {
  spacing
} = require('tailwindcss/defaultTheme');

const defaultTheme = require('tailwindcss/defaultTheme');

const colors = require('tailwindcss/colors');

const hyvaModules = require('@hyva-themes/hyva-modules');

module.exports = hyvaModules.mergeTailwindConfig({
  theme: {
    extend: {
      screens: {
        'xs': '540px',
        // => @media (min-width: 540px) { ... }
        'sm': '767px',
        // => @media (min-width: 767px) { ... }
        'md': '992px',
        // => @media (min-width: 992px) { ... }
        'lg': '1024px',
        // => @media (min-width: 1024px) { ... }
        'xl': '1171px',
        // => @media (min-width: 1171px) { ... }
        '2xl': '1266px', 
        // => @media (min-width: 1266px) { ... }
        'max-2xl': { 'max': '1266px' },
        'max-lg': { 'max': '1023px' },
        'max-xl': { 'max': '1170px' },
        'max-md': { 'max': '991px' },
        'max-sm': { 'max': '767px' },
        'max-xs': { 'max': '480px' },

      },
      container: {
        center: true,
        padding: {
          DEFAULT: '15px',
          sm: '15px',
          lg: '15px',
          xl: '15px',
          '2xl': '15px',
        },
      },
      fontFamily: {
        sans: ["Segoe UI", "Helvetica Neue", "Arial", "sans-serif"],
        'body': ['"UAESymbol","Figtree", sans-serif'],
        'uae-symbol': ['UAESymbol', 'sans-serif']
      },
      colors: {
        primary: {
          lighter: '#FF6A6D',
          DEFAULT: '#ED1C24',
          darker: '#B01015',
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        background: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        black: {
          brand: '#262626',
          "DEFAULT": '#000000',
          "100": '#101010',
        },
        gray: {
          "dark": '#262626',
          "DEFAULT": '#262626',
          "light": '#d4d4d4',
          '500': '#525252',
          "800": '#262626',
        },
        blue: {
          "700": '#1D4ED8',
        },
        neutral: {
          "600": '#525252',
        },
        red :{
          "600": '#ED1C24'
        },
        green: colors.emerald,
        yellow: colors.amber,
        purple: colors.violet
      },
      textColor: {
        black: {
          brand: '#262626',
          "DEFAULT": '#000000',
          "100": '#101010',
        },
        orange: colors.orange,
        red: {
          ...colors.red,
          "DEFAULT": colors.red['500']
        },
        primary: {
          lighter: '#FF6A6D',
          DEFAULT: '#ED1C24',
          darker: '#B01015',
        },
        secondary: {
          lighter: colors.gray['400'],
          "DEFAULT": colors.gray['600'],
          darker: colors.gray['800']
        },
        gray: {
          "dark": '#262626',
          "DEFAULT": '#262626',
          "light": '#d4d4d4',
          '500': '#525252',
          "800": '#262626',
        },
        red :{
          "600": '#ED1C24'
        },
        yellow :{
          '300': "#fed700",
          '600': "#ca8a04",
          "800": "#854d0e"
        },
        green :{
          '300': "#86efac",
        }
      },
      backgroundColor: {
        black: {
          brand: '#262626',
          "DEFAULT": '#000000',
        },
        primary: {
          lighter: '#FF6A6D',
          DEFAULT: '#ED1C24',
          darker: '#B01015',
          "100": "#FAE6E6",
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        container: {
          lighter: '#ffffff',
          "DEFAULT": '#fafafa',
          darker: '#f5f5f5'
        },
        gray: {
          "dark": '#262626',
          "DEFAULT": '#262626',
          "light": '#d4d4d4',
          "100": "#F6F6F6",
          "200": "#FBFBFB",
          '500': '#525252',
        },
        red :{
          "600": '#ED1C24'
        },
        yellow: {
          "50": "#FEF7E9",
          "300": "#fed700",
        },
        green: {
          '300': "#86efac",
        }
      },
      backgroundImage: {
        'arrow-up-right': "url('../images/arrow-up-right.svg')",
        'arrow-up-right-white': "url('../images/arrow-up-right-white.svg')",
        'white-pattern1': "url('../images/bg-pattern-white.webp')",
        'box-pattern1': "url('../images/box-pattern.webp')",
        "blue-pattern2": "url('../images/blue-bg-1.webp')",
        "orange-pattern2": "url('../images/orange-bg-1.webp')",
        "black-pattern2": "url('../images/black-bg-1.webp')",
        "sale-label": "url('../images/label-bg.webp')",
        "primary-circle-check-icon": "url('../images/primary-circle-check-icon.svg')",
      },
      borderColor: {
        black: {
          brand: '#262626',
          "DEFAULT": '#000000',
          "100": '#101010'
        },
        primary: {
          lighter: '#FF6A6D',
          DEFAULT: '#ED1C24',
          darker: '#B01015',
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        container: {
          lighter: '#f5f5f5',
          "DEFAULT": '#e7e7e7',
          darker: '#b6b6b6'
        },
        gray: {
          "dark": '#262626',
          "DEFAULT": '#262626',
          "light": '#d4d4d4',
          '500': '#525252',
        },
        yellow: {
          "300": "#fed700",
        },
      },
      minWidth: {
        8: spacing["8"],
        20: spacing["20"],
        40: spacing["40"],
        48: spacing["48"]
      },
      minHeight: {
        14: spacing["14"],
        a11y: '44px',
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh'
      },
      height: {
        '200': '200px'
      },
      maxHeight: {
        '0': '0',
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh',
        '400': '400px'
      },
      container: {
        center: true,
        padding: '1.5rem'
      },
      zIndex: {
        '0': '0',
        '10': '10',
      },
      display: ['peer-checked'],
    }
  },
  options: {
    safelist: ["inline", "p-12", "z-0", "z-10", "w-72", 'btn-size-lg', "bg-container-secondary", "gradient-gray-100", "bg-secondary-100", 'w-3/6', 'px-[30px]', 'order-[-1]','leading-[1.33]','bg-black-brand','max-md', 'bg-yellow-300'],
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
  // Examples for excluding patterns from purge
  mode: 'jit',
  content: [
    // this theme's phtml and layout XML files
    '../../**/*.phtml',
    '../../*/layout/*.xml',
    '../../*/page_layout/override/base/*.xml',
    // parent theme in Vendor (if this is a child-theme)
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/layout/*.xml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/page_layout/override/base/*.xml',
    '../../../../../../../vendor/mirasvit/module-product-kit-hyva/**/*.phtml',
    '../../../../../../../vendor/mirasvit/module-product-kit-hyva/**/*.xml',
    // app/code phtml files (if need tailwind classes from app/code modules)
    '../../../../../../../app/code/**/*.phtml',
  ]
});
