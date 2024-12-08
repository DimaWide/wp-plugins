
document.addEventListener('DOMContentLoaded', function () {

    /* 
    sct-7-promote
    */
    if (document.querySelector('.sct-7-promote')) {
        let section = document.querySelector('.sct-7-promote')
        let section_groove = document.querySelector('.sct-8-gooner')


        section.querySelector('form').addEventListener('submit', function (e) {
            e.preventDefault()

            let form = this;

            let mint = form.querySelector('input[name="mint"]').value

            let data_request = {
                action: 'project_load_posts',
                mint: mint,
            }

            // Show preloader
            const preloader = section_groove.querySelector('.preloader');
            preloader.style.display = 'flex';


            section_groove.querySelector('.data-b1').innerHTML = ''


            if (!section_groove.classList.contains('active')) {
                section_groove.classList.add('active')
            }

            if (section_groove.querySelector('.data-inner')) {
                section_groove.querySelector('.data-inner').classList.add('active')
            }

            form.querySelector('input[type="submit"]').setAttribute('disabled', 'disabled')
            form.querySelector('input[type="submit"]').classList.add('active')

            let xhr = new XMLHttpRequest();
            xhr.open('POST', wcl_obj.ajax_url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            xhr.onload = function (data) {
                if (xhr.status >= 200 && xhr.status < 400) {
                    var data = JSON.parse(xhr.responseText);

                    form.querySelector('input[type="submit"]').classList.remove('active')
                    form.querySelector('input[type="submit"]').removeAttribute('disabled')

                    section_groove.querySelector('.data-b1').innerHTML = data.token;
                    section_groove.setAttribute('data-mint', data.mint)

                    if (section_groove.querySelector('.data-inner').classList.contains('active')) {
                        section_groove.querySelector('.data-inner').classList.remove('active')
                    }

                    preloader.style.display = 'none';
                };
            };

            data_request = new URLSearchParams(data_request).toString();
            xhr.send(data_request);
        })
    }



    /* 
    sct-8-gooner
    */
    if (document.querySelector('.sct-8-gooner')) {
        let section = document.querySelector('.sct-8-gooner')

        section.querySelector('.data-link button').addEventListener('click', function () {
            let self = this
            let product = section.querySelector('.data-item.active')
            let notice = section.querySelector('.data-notice'); 

            if (!product) {
                if (!notice) {
                    notice = document.createElement('div');
                    notice.classList.add('data-notice');
                    section.querySelector('.data-inner').appendChild(notice);
                }

                notice.textContent = 'Please select a plan to continue.';
            } else {
                let plan = product.getAttribute('data-plan');

                if (notice) {
                    notice.remove()
                }

                let mint = section.getAttribute('data-mint')

                let data_request = {
                    action: 'np_create_payment',
                    mint: mint,
                    plan: plan,
                }

                if (section.querySelector('.data-inner')) {
                    section.querySelector('.data-inner').classList.add('active')
                }

                self.setAttribute('disabled', 'disabled')
                self.classList.add('active')

                let xhr = new XMLHttpRequest();
                xhr.open('POST', wcl_obj.ajax_url, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.onload = function (data) {
                    if (xhr.status >= 200 && xhr.status < 400) {
                        var data = JSON.parse(xhr.responseText);

                        self.removeAttribute('disabled')
                        self.classList.remove('active')

                        if (section.querySelector('.data-inner').classList.contains('active')) {
                            section.querySelector('.data-inner').classList.remove('active')
                        }

                        if (data.success) {
                            window.location.href = data.data.payment_url;
                        } else {
                            if (!notice) {
                                notice = document.createElement('div');
                                notice.classList.add('data-notice');
                                section.querySelector('.data-inner').appendChild(notice);
                            }

                            notice.textContent = data.data.error;
                        }
                    };
                };

                data_request = new URLSearchParams(data_request).toString();
                xhr.send(data_request);
            }
        });

        section.querySelectorAll('.data-item-inner').forEach(element => {
            element.addEventListener('click', function (e) {
                let self = element.parentElement
                let notice = section.querySelector('.data-notice');

                section.querySelectorAll('.data-item').forEach(element => {
                    element.classList.remove('active')
                });

                if (self.classList.contains('active')) {
                    self.classList.remove('active')
                } else {
                    self.classList.add('active')
                }

                if (notice) {
                    notice.remove()
                }
            })
        });
    }

});
