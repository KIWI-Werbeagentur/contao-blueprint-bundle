window.parent.addEventListener("blueprint_insert", (e) => {
    document.querySelector('body').classList.add('--blueprint_insert')

    document.querySelectorAll(".mod_article").forEach(article => {
        article.classList.add("--inactive")
    })

    window.parent.addEventListener("blueprint_preview", (e) => {
        document.querySelector(".mod_article:not(.--inactive)")?.classList.add("--inactive")

        const target = document.querySelector(`#${e.detail}`)
        target.classList.remove("--inactive")

        //window.parent.dispatchEvent(new CustomEvent("blueprint_preview_resize", {detail: {'height': target.getBoundingClientRect().height, 'layout':0}}))
    })

})
