export function setupFormHandler() {

    document.getElementById('aiml-form').addEventListener('submit', function (e) {
        e.preventDefault();

        let section = document.querySelector('.data-aiml-form')
        const prompt = document.getElementById('prompt').value;

        section.querySelector('button').classList.add('blink');
        section.querySelector('button').setAttribute('disabled', 'disabled')

        fetch(postManagerApi.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams({
                action: 'openai_generate_ai_text',
                prompt: prompt,
                security: postManagerApi.nonce_02
            })
        })
            .then(response => response.json())
            .then(data => {

                let text = data.data.text;

                if (data.success) {
                    let lines = text.split('\n');
                    lines.splice(0, 2);
                    text = lines.join('\n');
                }
            
                // document.getElementById('prompt').value = '';
                document.getElementById('result').value = text;
                section.querySelector('button').classList.remove('blink');
                section.querySelector('button').removeAttribute('disabled')
                document.getElementById('result-container').classList.remove('hidden');

             console.log(data)
            })
            .catch(error => console.error('Error:', error));
    });


    document.getElementById('copy-button').addEventListener('click', function () {
        const resultTextArea = document.getElementById('result');
        resultTextArea.select(); 
        document.execCommand('copy'); 

        alert('Text copied to clipboard!'); 
    });

}
