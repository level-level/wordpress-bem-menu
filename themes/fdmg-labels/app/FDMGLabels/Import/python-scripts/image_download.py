import os
import certifi
import pymysql
import urllib3


class DbConnector:
    def __init__(self, host_name: str, port_number: int, user_name: str, password: str, database: str):
        self.conn = pymysql.connect(host=host_name, port=port_number, user=user_name, passwd=password, db=database)

    def get_cursor(self):
        return self.conn.cursor()


class ImageExporter:
    def __init__(self, db_connector, source_url, target_folder):
        self.pool_manager = urllib3.PoolManager(cert_reqs="CERT_REQUIRED", ca_certs=certifi.where())
        self.db_connector = db_connector
        self.source_url = source_url
        self.target_folder = target_folder

    def download_file(self, id, file_path):
        foldername = str(id)[-3:]
        file_file = os.path.join(self.target_folder, foldername, os.sep.join(file_path.split('/')))

        if os.path.isfile(file_file):
            print(f"Ignoring {file_file}")
        else:
            url = f'{self.source_url}/{file_path}'
            file_folder = os.sep.join(file_file.split(os.sep)[0:-1])

            if not os.path.exists(file_folder):
                print(f'Creating folder {file_folder}')
                os.makedirs(file_folder)

            print(f"Downloading file from {url}")
            response = self.pool_manager.request('GET', url)

            print(f'Saving file to {file_file}')
            with open(file_file, 'wb') as f:
                f.write(response.data)

    def assemble_files(self):
        cursor = self.db_connector.get_cursor()

        cursor.execute(
            "SELECT id, path FROM picture WHERE state = 'PUBLISHED';")
        for row in cursor:
            self.download_file(row[0], row[1])
        cursor.close()


def energeia_dev_exporter():
    db_connector = DbConnector('localhost', 3306, 'root', 'root', "energeia-pre-wp")
    file_exporter = ImageExporter(db_connector, "https://energeia-external-prod.imgix.net", "/srv/http/shared/www/wp-content/uploads/sites/2/imported")

    return file_exporter


if __name__ == '__main__':
    energeia_dev_exporter().assemble_files()
