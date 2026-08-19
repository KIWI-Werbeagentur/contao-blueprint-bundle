window.parent.addEventListener("blueprint_insert", () => {
    document.querySelector('body').classList.add('--blueprint_insert')

    document.querySelectorAll(".mod_article").forEach(article => {
        article.classList.add("--inactive")
    })
})

window.parent.addEventListener("blueprint_preview", (e) => {
    const target = document.querySelector(`#${CSS.escape(e.detail)}`)
    if (target) {
        target.classList.remove("--inactive")
    } else {
        document.querySelectorAll(".mod_article").forEach(article => {
            article.classList.remove("--inactive")
        })
    }
})
