import os
import re

dir_path = r'd:\01. Project\04. Website\pln\webapp\src'
pattern = re.compile(r'/api/')

for root, dirs, files in os.walk(dir_path):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            replaced = pattern.sub(r'<?= API_URL ?>/api/', content)
            
            if replaced != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(replaced)
                print(f'Fixed {filepath}')
