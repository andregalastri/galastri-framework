<style>
    html {
        box-sizing: border-box;
        -ms-overflow-style: scrollbar;
        height: 100%;
        font-family: sans-serif;
    }

    *,
    *::before,
    *::after {
        box-sizing: inherit;
    }

    body {
        color: #fff;
        height: 100%;
        margin: 0;
        padding: 1px;
        background: #393b40;
        -webkit-font-smoothing: antialiased;
        display: flex;
    }


    .main {
        margin: auto;
        flex-direction: column;
        display: flex;
        align-items: center;
        opacity: 0;
        animation-name: animationOpacity, animationTranslateY;
        animation-delay: 200ms, 200ms;
        animation-duration: 1500ms, 1500ms;
        animation-fill-mode: forwards, forwards;
    }

    header {
        flex-direction: column;
        display: flex;
        align-items: center;
    }

    header h1 {
        background-image: url('images/galastri-framework.png');
        background-repeat: no-repeat;
        background-size: contain;
        margin: 0;
        padding: 0;
        width: 230px;
        height: 94px;

    }

    header span {
        margin-top: 20px;
        color: #c6c4c4;
        font-size: 11px;
        letter-spacing: 3px;
        margin-left: 9px;
    }

    .version {
        position: fixed;
        color: #c6c4c4;
        font-size: 13px;
        transform: scaleY(1.8);
        letter-spacing: 0 !important;
        opacity: 0.6;
        bottom: 15px;
        right: 15px;
        text-align: right;
    }

    nav,
    .info {
        margin-top: 35px;
        display: flex;
        flex-direction: column;
        width: 100%;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        animation-name: animationOpacity, animationTranslateY;
        animation-duration: 1500ms, 1500ms;
        animation-fill-mode: forwards, none;
        transition: all 200ms;
        position: relative;
        background: rgba(0, 0, 0, 0.15);
    }

    .info {
        animation: none;
        color: #c4c4c4;
        padding: 10px 15px;
        align-items: center;
        position: absolute;
        bottom: -40px;
        font-size: 13px;
        opacity: 0;
        font-weight: bold;
        background: rgba(0, 0, 0, 0.15);
        transition: all 500ms;
    }

    .info.active {
        opacity: 1;
    }

    nav .button {
        background: none;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        color: #bfbfbf;
        font-family: sans-serif;
        cursor: pointer;
        transition: all 200ms;
        font-size: 13px;
        font-weight: bold;
        opacity: 0;
        animation-name: animationOpacity, animationTranslateY;
        animation-duration: 1500ms, 1500ms;
        animation-fill-mode: forwards, none;
        margin: 0 1px;
        transform: translateY(0);
        flex-grow: 1;
        padding: 10px 25px;
    }

    nav .button:hover {
        transform: translateY(-2px);
    }

    nav .button:nth-child(2) {
        animation-delay: 600ms;
    }
    nav .button:nth-child(3) {
        animation-delay: 800ms;
    }

    nav .button .icon {
        width: 22px;
        height: 22px;
        background-size: contain;
        opacity: 0.8;
        margin-right: 5px;
    }

    nav .button .icon.docs {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTEyIDUxMjsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiBjbGFzcz0iIj48Zz48Zz4KCTxnPgoJCTxnPgoJCQk8cGF0aCBkPSJNMzUyLjQ1OSwyMjBjMC0xMS4wNDYtOC45NTQtMjAtMjAtMjBoLTIwNmMtMTEuMDQ2LDAtMjAsOC45NTQtMjAsMjBzOC45NTQsMjAsMjAsMjBoMjA2ICAgICBDMzQzLjUwNSwyNDAsMzUyLjQ1OSwyMzEuMDQ2LDM1Mi40NTksMjIweiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImFjdGl2ZS1wYXRoIiBzdHlsZT0iZmlsbDojRkZGRkZGIiBkYXRhLW9sZF9jb2xvcj0iIzAwMDAwMCI+PC9wYXRoPgoJCQk8cGF0aCBkPSJNMTI2LjQ1OSwyODBjLTExLjA0NiwwLTIwLDguOTU0LTIwLDIwYzAsMTEuMDQ2LDguOTU0LDIwLDIwLDIwSDI1MS41N2MxMS4wNDYsMCwyMC04Ljk1NCwyMC0yMGMwLTExLjA0Ni04Ljk1NC0yMC0yMC0yMCAgICAgSDEyNi40NTl6IiBkYXRhLW9yaWdpbmFsPSIjMDAwMDAwIiBjbGFzcz0iYWN0aXZlLXBhdGgiIHN0eWxlPSJmaWxsOiNGRkZGRkYiIGRhdGEtb2xkX2NvbG9yPSIjMDAwMDAwIj48L3BhdGg+CgkJCTxwYXRoIGQ9Ik0xNzMuNDU5LDQ3MkgxMDYuNTdjLTIyLjA1NiwwLTQwLTE3Ljk0NC00MC00MFY4MGMwLTIyLjA1NiwxNy45NDQtNDAsNDAtNDBoMjQ1Ljg4OWMyMi4wNTYsMCw0MCwxNy45NDQsNDAsNDB2MTIzICAgICBjMCwxMS4wNDYsOC45NTQsMjAsMjAsMjBjMTEuMDQ2LDAsMjAtOC45NTQsMjAtMjBWODBjMC00NC4xMTItMzUuODg4LTgwLTgwLTgwSDEwNi41N2MtNDQuMTEyLDAtODAsMzUuODg4LTgwLDgwdjM1MiAgICAgYzAsNDQuMTEyLDM1Ljg4OCw4MCw4MCw4MGg2Ni44ODljMTEuMDQ2LDAsMjAtOC45NTQsMjAtMjBDMTkzLjQ1OSw0ODAuOTU0LDE4NC41MDUsNDcyLDE3My40NTksNDcyeiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImFjdGl2ZS1wYXRoIiBzdHlsZT0iZmlsbDojRkZGRkZGIiBkYXRhLW9sZF9jb2xvcj0iIzAwMDAwMCI+PC9wYXRoPgoJCQk8cGF0aCBkPSJNNDY3Ljg4NCwyODkuNTcyYy0yMy4zOTQtMjMuMzk0LTYxLjQ1OC0yMy4zOTUtODQuODM3LTAuMDE2bC0xMDkuODAzLDEwOS41NmMtMi4zMzIsMi4zMjctNC4wNTIsNS4xOTMtNS4wMSw4LjM0NSAgICAgbC0yMy45MTMsNzguNzI1Yy0yLjEyLDYuOTgtMC4yNzMsMTQuNTU5LDQuODIxLDE5Ljc4YzMuODE2LDMuOTExLDksNi4wMzQsMTQuMzE3LDYuMDM0YzEuNzc5LDAsMy41NzUtMC4yMzgsNS4zMzgtMC43MjcgICAgIGw4MC43MjUtMjIuMzYxYzMuMzIyLTAuOTIsNi4zNS0yLjY4Myw4Ljc5LTUuMTE5bDEwOS41NzMtMTA5LjM2N0M0OTEuMjc5LDM1MS4wMzIsNDkxLjI3OSwzMTIuOTY4LDQ2Ny44ODQsMjg5LjU3MnogICAgICBNMzMzLjc3Niw0NTEuNzY4bC00MC42MTIsMTEuMjVsMTEuODg1LTM5LjEyOWw3NC4wODktNzMuOTI1bDI4LjI5LDI4LjI5TDMzMy43NzYsNDUxLjc2OHogTTQzOS42MTUsMzQ2LjEzbC0zLjg3NSwzLjg2NyAgICAgbC0yOC4yODUtMjguMjg1bDMuODYyLTMuODU0YzcuNzk4LTcuNzk4LDIwLjQ4Ni03Ljc5OCwyOC4yODQsMEM0NDcuMzk5LDMyNS42NTYsNDQ3LjM5OSwzMzguMzQ0LDQzOS42MTUsMzQ2LjEzeiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImFjdGl2ZS1wYXRoIiBzdHlsZT0iZmlsbDojRkZGRkZGIiBkYXRhLW9sZF9jb2xvcj0iIzAwMDAwMCI+PC9wYXRoPgoJCQk8cGF0aCBkPSJNMzMyLjQ1OSwxMjBoLTIwNmMtMTEuMDQ2LDAtMjAsOC45NTQtMjAsMjBzOC45NTQsMjAsMjAsMjBoMjA2YzExLjA0NiwwLDIwLTguOTU0LDIwLTIwUzM0My41MDUsMTIwLDMzMi40NTksMTIweiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImFjdGl2ZS1wYXRoIiBzdHlsZT0iZmlsbDojRkZGRkZGIiBkYXRhLW9sZF9jb2xvcj0iIzAwMDAwMCI+PC9wYXRoPgoJCTwvZz4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+");
    }

    nav .button .icon.github {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDI0IDI0IiBoZWlnaHQ9IjUxMiIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iNTEyIiBjbGFzcz0iIj48Zz48cGF0aCBkPSJtMTIgLjVjLTYuNjMgMC0xMiA1LjI4LTEyIDExLjc5MiAwIDUuMjExIDMuNDM4IDkuNjMgOC4yMDUgMTEuMTg4LjYuMTExLjgyLS4yNTQuODItLjU2NyAwLS4yOC0uMDEtMS4wMjItLjAxNS0yLjAwNS0zLjMzOC43MTEtNC4wNDItMS41ODItNC4wNDItMS41ODItLjU0Ni0xLjM2MS0xLjMzNS0xLjcyNS0xLjMzNS0xLjcyNS0xLjA4Ny0uNzMxLjA4NC0uNzE2LjA4NC0uNzE2IDEuMjA1LjA4MiAxLjgzOCAxLjIxNSAxLjgzOCAxLjIxNSAxLjA3IDEuODAzIDIuODA5IDEuMjgyIDMuNDk1Ljk4MS4xMDgtLjc2My40MTctMS4yODIuNzYtMS41NzctMi42NjUtLjI5NS01LjQ2Ni0xLjMwOS01LjQ2Ni01LjgyNyAwLTEuMjg3LjQ2NS0yLjMzOSAxLjIzNS0zLjE2NC0uMTM1LS4yOTgtLjU0LTEuNDk3LjEwNS0zLjEyMSAwIDAgMS4wMDUtLjMxNiAzLjMgMS4yMDkuOTYtLjI2MiAxLjk4LS4zOTIgMy0uMzk4IDEuMDIuMDA2IDIuMDQuMTM2IDMgLjM5OCAyLjI4LTEuNTI1IDMuMjg1LTEuMjA5IDMuMjg1LTEuMjA5LjY0NSAxLjYyNC4yNCAyLjgyMy4xMiAzLjEyMS43NjUuODI1IDEuMjMgMS44NzcgMS4yMyAzLjE2NCAwIDQuNTMtMi44MDUgNS41MjctNS40NzUgNS44MTcuNDIuMzU0LjgxIDEuMDc3LjgxIDIuMTgyIDAgMS41NzgtLjAxNSAyLjg0Ni0uMDE1IDMuMjI5IDAgLjMwOS4yMS42NzguODI1LjU2IDQuODAxLTEuNTQ4IDguMjM2LTUuOTcgOC4yMzYtMTEuMTczIDAtNi41MTItNS4zNzMtMTEuNzkyLTEyLTExLjc5MnoiIGZpbGw9IiMyMTIxMjEiIGRhdGEtb3JpZ2luYWw9IiMyMTIxMjEiIGNsYXNzPSJhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6I0ZGRkZGRiIgZGF0YS1vbGRfY29sb3I9IiMyMTIxMjEiPjwvcGF0aD48L2c+IDwvc3ZnPg==");
    }

    @media (min-width: 425px) {
        nav {
            flex-direction: row;
        }
    }

    @media (min-width: 768px) {
        header h1 {
            width: 350px;
            height: 143px;
        }

        header span {
            letter-spacing: 9px;
        }
    }

    @keyframes animationTranslateY {
        from {
            transform: translateY(-20px);
        }

        to {
            transform: translateY(0);
        }
    }

    @keyframes animationOpacity {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>

<script>
    function load(){
        var buttons = document.getElementsByClassName("button");
        var info = document.getElementsByClassName("info")[0];

        for(var i=0; i<buttons.length; i++){
            var button = buttons[i];

            button.addEventListener("mouseenter", function(event){
                var message = this.getAttribute('info');

                if(typeof message != 'undefined' && message != null){
                    info.classList.add('active');
                    info.innerHTML = message;
                }
            });

            button.addEventListener("mouseleave", function(event){
                info.classList.remove('active');
            });
        }
    }
</script>

<div class="main">
    <header>
        <h1></h1>
        <span>A PHP 7 MICROFRAMEWORK</span>
    </header>

    <nav>
        <div class="info"></div>

        <a class="button" info="Coming soon...">
            <div class="icon docs"></div>
            <span>DOCUMENTATION</span>
        </a>

        <a class="button" href="https://github.com/andregalastri/galastri-framework" target="_blank">
            <div class="icon github"></div>
            <span>GITHUB</span>
        </a>
    </nav>
</div>

<div class='version'><?php $galastri->print('version');?></div>

<script>load();</script>
