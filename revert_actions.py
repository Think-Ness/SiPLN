import os

dir_path = r'd:\01. Project\04. Website\pln\webapp\src'

for root, dirs, files in os.walk(dir_path):
    for file in files:
        if file.endswith('Action.php') or file == 'AuthMiddleware.php' or file == 'tmp_clean.php':
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            replaced = content.replace('<?= API_URL ?>', '')
            
            if replaced != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(replaced)
                print(f'Reverted {filepath}')
