<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-container">
                <div class="modal-header">
                    <slot name="header">
                    </slot>
                </div>
                <div class="modal-body">
                    <slot name="body">
                    </slot>
                </div>
                <div class="modal-footer">
                    <slot name="footer">
                    </slot>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>

  export default {}
</script>
<style>
    .modal-mask {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9998;
        width: 100%;
        height: 100%;
        transition: opacity .3s ease;
    }

    .modal-container {
        background: #FFFFFF;
        overflow-x: auto;
        display: flex;
        flex-direction: column;
        width: 13%;
        height: 27%;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        transition: all .3s ease;
        font-family: Helvetica, Arial, sans-serif;
        margin-left: 40%;
        margin-top: 12%;

    }

    .modal-footer {
        border-top: 1px solid #eeeeee;
        justify-content: flex-end;
        margin: 0;
        padding: 0;
    }

    .modal-header {
        border-bottom: 1px solid #f0f3f5;
        color: #4AAE9B;
        justify-content: space-between;
        margin: 0;
        padding: 0;
    }

    .modal-body {
        position: relative;
        margin: 0;
        padding: 0;
    }


</style>

