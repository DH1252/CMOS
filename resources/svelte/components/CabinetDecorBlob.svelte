<script>
  let { class: className = "", id, variant = "capsule" } = $props();
</script>

{#if variant === "diamond"}
  <svg
    class={className}
    viewBox="0 0 139.825 518.751"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
  >
    <path
      d="M69.9132 0L139.825 121.042L69.9132 242.084L0.00113267 121.042L69.9132 0Z"
      fill={`url(#${id}-a)`}
    />
    <path
      d="M69.9121 518.751L139.824 397.709L69.9121 276.668L0.0000637445 397.709L69.9121 518.751Z"
      fill={`url(#${id}-b)`}
    />
    <defs>
      <linearGradient
        id={`${id}-a`}
        x1="36.4137"
        y1="51.3568"
        x2="170.814"
        y2="146.209"
        gradientUnits="userSpaceOnUse"
      >
        <stop stop-color="var(--orange)" />
        <stop offset="1" stop-color="var(--yellow)" />
      </linearGradient>
      <linearGradient
        id={`${id}-b`}
        x1="36.4126"
        y1="467.394"
        x2="170.813"
        y2="372.542"
        gradientUnits="userSpaceOnUse"
      >
        <stop stop-color="var(--orange)" />
        <stop offset="1" stop-color="var(--yellow)" />
      </linearGradient>
    </defs>
  </svg>
{:else if variant === "tall-diamond"}
  <svg
    class={className}
    viewBox="0 0 113.846 658.679"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
  >
    <path
      d="M56.9228 0.0000760308L113.846 153.691L56.9228 307.382L0.000175436 153.691L56.9228 0.0000760308Z"
      fill={`url(#${id}-a)`}
    />
    <path
      d="M56.9236 658.679L113.846 504.989L56.9236 351.298L0.000894797 504.989L56.9236 658.679Z"
      fill={`url(#${id}-b)`}
    />
    <defs>
      <linearGradient
        id={`${id}-a`}
        x1="29.6474"
        y1="65.2095"
        x2="165.713"
        y2="126.786"
        gradientUnits="userSpaceOnUse"
      >
        <stop stop-color="var(--orange)" />
        <stop offset="1" stop-color="var(--yellow)" />
      </linearGradient>
      <linearGradient
        id={`${id}-b`}
        x1="29.6481"
        y1="593.47"
        x2="165.714"
        y2="531.893"
        gradientUnits="userSpaceOnUse"
      >
        <stop stop-color="var(--orange)" />
        <stop offset="1" stop-color="var(--yellow)" />
      </linearGradient>
    </defs>
  </svg>
{:else}
  <svg
    class={className}
    viewBox="0 0 301.137 117.745"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
  >
    <path
      d="M0.0000754742 56.1572L44.0628 33.8596C81.0163 15.1596 128.268 26.257 149.603 58.6463L105.541 80.9439C68.5873 99.6439 21.3351 88.5465 0.0000754742 56.1572Z"
      fill={`url(#${id}-a)`}
    />
    <path
      d="M301.137 61.588L257.074 83.8857C220.121 102.586 172.869 91.4882 151.534 59.0989L195.597 36.8013C232.55 18.1013 279.802 29.1987 301.137 61.588Z"
      fill={`url(#${id}-b)`}
    />
    <defs>
      <linearGradient
        id={`${id}-a`}
        x1="37.0945"
        y1="53.9744"
        x2="99.2154"
        y2="101.306"
        gradientUnits="userSpaceOnUse"
      >
        <stop stop-color="var(--orange)" />
        <stop offset="1" stop-color="var(--yellow)" />
      </linearGradient>
      <linearGradient
        id={`${id}-b`}
        x1="264.043"
        y1="63.7708"
        x2="201.922"
        y2="16.4392"
        gradientUnits="userSpaceOnUse"
      >
        <stop stop-color="var(--orange)" />
        <stop offset="1" stop-color="var(--yellow)" />
      </linearGradient>
    </defs>
  </svg>
{/if}
