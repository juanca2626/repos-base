<script lang="ts" setup>
  const props = defineProps<{ amenities?: [] }>();

  const filterAmenities = (page) => {
    return props.amenities?.slice((page - 1) * 6, page * 6);
  };

  filterAmenities(1);
  filterAmenities(2);
</script>

<template>
  <div class="rowTab">
    <div class="itemRowTab">
      <div v-for="(amenity, i) in filterAmenities(1)" :key="i">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="21"
          viewBox="0 0 20 21"
          fill="none"
        >
          <path
            d="M17.0827 3.83325H2.91602C2.5845 3.83325 2.26655 3.96495 2.03213 4.19937C1.79771 4.43379 1.66602 4.75173 1.66602 5.08325L1.66602 13.4166C1.66602 13.7481 1.79771 14.066 2.03213 14.3005C2.26655 14.5349 2.5845 14.6666 2.91602 14.6666H9.16602V15.4999H4.58268C4.47218 15.4999 4.36619 15.5438 4.28805 15.622C4.20991 15.7001 4.16602 15.8061 4.16602 15.9166V16.7499C4.16602 16.8604 4.20991 16.9664 4.28805 17.0445C4.36619 17.1227 4.47218 17.1666 4.58268 17.1666H15.416C15.5265 17.1666 15.6325 17.1227 15.7106 17.0445C15.7888 16.9664 15.8327 16.8604 15.8327 16.7499V15.9166C15.8327 15.8061 15.7888 15.7001 15.7106 15.622C15.6325 15.5438 15.5265 15.4999 15.416 15.4999H10.8327V14.6666H17.0827C17.4142 14.6666 17.7321 14.5349 17.9666 14.3005C18.201 14.066 18.3327 13.7481 18.3327 13.4166V5.08325C18.3327 4.75173 18.201 4.43379 17.9666 4.19937C17.7321 3.96495 17.4142 3.83325 17.0827 3.83325V3.83325ZM16.666 12.9999H3.33268V5.49992H16.666V12.9999Z"
            fill="#7E8285"
          />
        </svg>
        {{ amenity.name }}
      </div>
    </div>

    <div class="itemRowTab">
      <div v-for="(amenity, i) in filterAmenities(2)" :key="i">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="21"
          viewBox="0 0 20 21"
          fill="none"
        >
          <path
            d="M17.0827 3.83325H2.91602C2.5845 3.83325 2.26655 3.96495 2.03213 4.19937C1.79771 4.43379 1.66602 4.75173 1.66602 5.08325L1.66602 13.4166C1.66602 13.7481 1.79771 14.066 2.03213 14.3005C2.26655 14.5349 2.5845 14.6666 2.91602 14.6666H9.16602V15.4999H4.58268C4.47218 15.4999 4.36619 15.5438 4.28805 15.622C4.20991 15.7001 4.16602 15.8061 4.16602 15.9166V16.7499C4.16602 16.8604 4.20991 16.9664 4.28805 17.0445C4.36619 17.1227 4.47218 17.1666 4.58268 17.1666H15.416C15.5265 17.1666 15.6325 17.1227 15.7106 17.0445C15.7888 16.9664 15.8327 16.8604 15.8327 16.7499V15.9166C15.8327 15.8061 15.7888 15.7001 15.7106 15.622C15.6325 15.5438 15.5265 15.4999 15.416 15.4999H10.8327V14.6666H17.0827C17.4142 14.6666 17.7321 14.5349 17.9666 14.3005C18.201 14.066 18.3327 13.7481 18.3327 13.4166V5.08325C18.3327 4.75173 18.201 4.43379 17.9666 4.19937C17.7321 3.96495 17.4142 3.83325 17.0827 3.83325V3.83325ZM16.666 12.9999H3.33268V5.49992H16.666V12.9999Z"
            fill="#7E8285"
          />
        </svg>
        {{ amenity.name }}
      </div>
    </div>
  </div>
</template>

<style lang="scss">
  .more {
    color: #55a3ff;
    font-family: Montserrat;
    font-size: 16px;
    font-style: normal;
    font-weight: 500;
    line-height: 23px; /* 143.75% */
    letter-spacing: -0.24px;
    position: absolute;
    left: 0;
    bottom: -60px;
    text-decoration-line: underline;
    cursor: pointer;
  }

  .rowTab {
    display: block;
    width: 100%;

    .itemRowTab {
      position: relative;
      display: inline-block;
      padding: 0 16px;
      margin-top: 16px;
      align-items: flex-start;
      gap: 16px;
      align-self: stretch;
      border-radius: 8px;
      flex-direction: column;
      flex: 1 0 0;
      border: 1px solid #e7e7e7;
      background: #fff;
      vertical-align: top;
      width: 48.5%;

      &:last-child {
        margin-left: 3%;
      }

      div {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        align-self: stretch;
        border-bottom: 1px solid #e7e7e7;
        padding: 16px 0 16px 0;

        &:last-child {
          border-bottom: 0;
          padding: 16px 0;
        }
      }
    }
  }
</style>
