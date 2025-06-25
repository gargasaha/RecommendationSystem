from flask import Flask, request, render_template, session,redirect
import pandas as pd
import random
import mysql.connector
from mysql.connector import Error
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)

trending_products = pd.read_csv("models/trending_products.csv")
train_data = pd.read_csv("models/clean_data.csv")

app.secret_key = "alskdjfwoeieiurlskdjfslkdjf"

def get_db_connection():
    return mysql.connector.connect(
        host='localhost',
        user='root',
        password='9932',
        database='ProductRecommendationSystem'
    )

def truncate(text, length):
    if len(text) > length:
        return text[:length] + "..."
    else:
        return text

def content_based_recommendations(train_data, item_name, top_n=10):
    if item_name not in train_data['Name'].values:
        print(f"Item '{item_name}' not found in the training data.")
        return pd.DataFrame()
    tfidf_vectorizer = TfidfVectorizer(stop_words='english')
    tfidf_matrix_content = tfidf_vectorizer.fit_transform(train_data['Tags'])
    cosine_similarities_content = cosine_similarity(tfidf_matrix_content, tfidf_matrix_content)
    item_index = train_data[train_data['Name'] == item_name].index[0]
    similar_items = list(enumerate(cosine_similarities_content[item_index]))
    similar_items = sorted(similar_items, key=lambda x: x[1], reverse=True)
    top_similar_items = similar_items[1:top_n+1]
    recommended_item_indices = [x[0] for x in top_similar_items]
    recommended_items_details = train_data.iloc[recommended_item_indices][['ID','Name', 'ReviewCount', 'Brand', 'ImageURL', 'Rating']]
    return recommended_items_details

random_image_urls = [
    "static/img/img_1.png",
    "static/img/img_2.png",
    "static/img/img_3.png",
    "static/img/img_4.png",
    "static/img/img_5.png",
    "static/img/img_6.png",
    "static/img/img_7.png",
    "static/img/img_8.png",
]

@app.route("/")
def index():
    random_product_image_urls = [random.choice(random_image_urls) for _ in range(len(trending_products))]
    price = [40, 50, 60, 70, 100, 122, 106, 50, 30, 50]
    return render_template('index.html',trending_products=trending_products.head(8),truncate = truncate,
                           random_product_image_urls=random_product_image_urls,
                           random_price = random.choice(price),
                           name=session['username'] if 'username' in session else None)

@app.route("/main")
def main():
    return redirect("/recommendations")

@app.route("/index")
def indexredirect():
    random_product_image_urls = [random.choice(random_image_urls) for _ in range(len(trending_products))]
    price = [40, 50, 60, 70, 100, 122, 106, 50, 30, 50]
    return render_template('index.html', trending_products=trending_products.head(8), truncate=truncate,
                           random_product_image_urls=random_product_image_urls,
                           random_price=random.choice(price),
                           name=session['username'] if 'username' in session else None)

@app.route("/signup", methods=['POST','GET'])
def signup():
    if request.method == 'POST':
        username = request.form['username']
        email = request.form['email']
        password = request.form['password']
        category = request.form['category']
        try:
            conn = get_db_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT * FROM signup WHERE username=%s", (username,))   
            existing_user = cursor.fetchone()
            if existing_user:
                cursor.close()
                conn.close()
                return render_template('main.html', message="Username already exists. Please choose a different username.") 
            cursor.execute("SELECT * FROM signup WHERE email=%s", (email,))
            existing_email = cursor.fetchone()
            if existing_email:
                cursor.close()
                conn.close()
                return render_template('main.html', message="Email already exists. Please use a different email address.")
            cursor = conn.cursor()
            cursor.execute(
                "INSERT INTO signup (username, email, password, category) VALUES (%s, %s, %s, %s)",
                (username, email, password, category)
            )
            conn.commit()
            cursor.execute("SELECT id FROM signup WHERE username=%s AND password=%s", (username, password))
            userId_row = cursor.fetchone()
            userId = userId_row[0] if userId_row else None
            cursor.execute("insert into signin (id,username, password) values (%s, %s, %s)",
                           (userId, username, password))
            conn.commit()
            session['id'] = cursor.lastrowid
            session['username'] = username
            session['category'] = category
            category = session.get('category')
            if not category:
                category = train_data['Category'].mode()[0] if not train_data.empty else "Unknown"
            categories = [c.strip().lower() for c in str(category).split(',') if c.strip()]
            mask = False
            for cat in categories:
                if cat in ['beauty', 'appliances', 'health', 'personal']:
                    mask = mask | train_data['Category'].fillna('').str.lower().str.contains(cat, na=False)
                else:
                    mask = mask | (train_data['Category'].fillna('').str.lower() == cat)
            category_products = train_data[mask]
            recommended_items = category_products.head(20)[['ID','Name', 'ReviewCount', 'Brand', 'ImageURL', 'Rating']]
            random_product_image_urls = [random.choice(random_image_urls) for _ in range(len(recommended_items))]
            price = [40, 50, 60, 70, 100, 122, 106, 50, 30, 50]
            cursor.close()
            conn.close()

            return redirect('/recommendations')
        except Error as e:
            return render_template('main.html', message=f"Database error: {e}")
    elif request.method == 'GET':
        return redirect('/recommendations')
    return render_template('main.html')

@app.route('/signout', methods=['POST', 'GET'])
def signout():
    session.pop('id', None)
    session.pop('username', None)
    session.pop('category', None)
    return redirect('/index')

@app.route('/signin', methods=['POST', 'GET'])
def signin():
    if request.method == 'POST':
        username = request.form['signinUsername']
        password = request.form['signinPassword']
        try:
            conn = get_db_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(
                "SELECT * FROM signup WHERE username=%s AND password=%s",
                (username, password)
            )
            user = cursor.fetchone()
            if user:
                conn = get_db_connection()
                cursor = conn.cursor()
                cursor.execute('select count(*) from addToCart where id=%s', (user['id'],))
                count = cursor.fetchall()
                count = count[0][0] if count else 0
            
                selected_category = user['category']
                session['id'] = user['id']
                session['username'] = user['username']
                session['category'] = user['category']
                prod = request.form.get('prod')
                nbr_raw = request.form.get('nbr')
                if (not prod or prod.strip() == "") and (not nbr_raw or nbr_raw.strip() == ""):
                    category = session.get('category')
                    if not category:
                        category = train_data['Category'].mode()[0] if not train_data.empty else "Unknown"
                    categories = [c.strip().lower() for c in str(category).split(',') if c.strip()]
                    mask = False
                    for cat in categories:
                        if cat in ['beauty', 'appliances', 'health', 'personal']:
                            mask = mask | train_data['Category'].fillna('').str.lower().str.contains(cat, na=False)
                        else:
                            mask = mask | (train_data['Category'].fillna('').str.lower() == cat)
                    category_products = train_data[mask]
                    recommended_items = category_products.head(20)[['ID','Name', 'ReviewCount', 'Brand', 'ImageURL', 'Rating']]
                    random_product_image_urls = [random.choice(random_image_urls) for _ in range(len(recommended_items))]
                    price = [40, 50, 60, 70, 100, 122, 106, 50, 30, 50]
                    cursor.close()
                    conn.close()


                    

                    return redirect('/recommendations')
                else:
                    category_products = train_data[train_data['Category'].fillna('').str.lower() == str(selected_category).lower()]
                    recommended_items = category_products.head(8)[['ID','Name', 'ReviewCount', 'Brand', 'ImageURL', 'Rating']]
                    random_product_image_urls = [random.choice(random_image_urls) for _ in range(len(recommended_items))]
                    price = [40, 50, 60, 70, 100, 122, 106, 50, 30, 50]
                    cursor.close()
                    conn.close()
                    return redirect('/recommendations')
            else:
                cursor.close()
                conn.close()
                return render_template('main.html', message="Invalid credentials.")
        except Error as e:
            return render_template('main.html', message=f"Database error: {e}")

    return render_template('main.html')

@app.route("/recommendations", methods=['POST', 'GET'])
def recommendations():
    if request.method == 'POST':
        prod = request.form.get('prod')
        nbr_raw = request.form.get('nbr')
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('select count(*) from addToCart where id=%s', (session['id'],))
        count = cursor.fetchall()
        count = count[0][0] if count else 0
        try:
            nbr = int(nbr_raw)
            if nbr <= 0:
                raise ValueError
        except (ValueError, TypeError):
            return render_template('main.html', message="Please enter a valid number of recommendations.")
        if not prod or prod.strip() == "":
            category = session.get('category')
            if not category:
                category = train_data['Category'].mode()[0] if not train_data.empty else "Unknown"
            categories = [c.strip().lower() for c in str(category).split(',') if c.strip()]
            mask = False
            for cat in categories:
                if cat in ['beauty', 'appliances', 'health', 'personal']:
                    mask = mask | train_data['Category'].fillna('').str.lower().str.contains(cat, na=False)
                else:
                    mask = mask | (train_data['Category'].fillna('').str.lower() == cat)
            category_products = train_data[mask]
            recommended_items = category_products.head(nbr)[['ID','Name', 'ReviewCount', 'Brand', 'ImageURL', 'Rating']]
            random_product_image_urls = [random.choice(random_image_urls) for _ in range(len(recommended_items))]
            price = [40, 50, 60, 70, 100, 122, 106, 50, 30, 50]
            return render_template('main.html',
                                   content_based_rec=recommended_items,
                                   truncate=truncate,
                                   random_product_image_urls=random_product_image_urls,
                                   random_price=random.choice(price),
                                   message=f"Cold start: Showing products in '{category}'",
                                   name=session['username'] if 'username' in session else None,
                                   cart_count=count,
                                   f=1)
        content_based_rec = content_based_recommendations(train_data, prod, top_n=nbr)
        if content_based_rec.empty:
            return render_template('main.html', message="No recommendations available for this product.",
                                   name=session['username'] if 'username' in session else None)
        else:
            random_product_image_urls = [random.choice(random_image_urls) for _ in range(len(content_based_rec))]
            price = [40, 50, 60, 70, 100, 122, 106, 50, 30, 50]
            return render_template('main.html',
                                   content_based_rec=content_based_rec,
                                   truncate=truncate,
                                   random_product_image_urls=random_product_image_urls,
                                   random_price=random.choice(price),
                                   name=session['username'] if 'username' in session else None,
                                   cart_count=count)
    elif request.method == 'GET':
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('select count(*) from addToCart where id=%s', (session['id'],))
        count = cursor.fetchall()
        count = count[0][0] if count else 0
        category = session.get('category')
        if not category:
            category = train_data['Category'].mode()[0] if not train_data.empty else "Unknown"
        categories = [c.strip().lower() for c in str(category).split(',') if c.strip()]
        mask = False
        for cat in categories:
            if cat in ['beauty', 'appliances', 'health', 'personal']:
                mask = mask | train_data['Category'].fillna('').str.lower().str.contains(cat, na=False)
            else:
                mask = mask | (train_data['Category'].fillna('').str.lower() == cat)
        category_products = train_data[mask]
        recommended_items = category_products.head(20)[['ID','Name', 'ReviewCount', 'Brand', 'ImageURL', 'Rating']]
        random_product_image_urls = [random.choice(random_image_urls) for _ in range(len(recommended_items))]
        price = [40, 50, 60, 70, 100, 122, 106, 50, 30, 50]
        print(count)
        return render_template('main.html',
                               content_based_rec=recommended_items,
                               truncate=truncate,
                               random_product_image_urls=random_product_image_urls,
                               random_price=random.choice(price),
                               message=f"Showing products in '{category}'",
                               name=session['username'] if 'username' in session else None,
                               cart_count=count,
                               f=1)
    return render_template('main.html')

@app.route('/cart', methods=['GET'])
def cart():
    if 'id' not in session:
        return redirect('/signin')
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM addToCart WHERE id=%s", (session['id'],))
        cart_items = cursor.fetchall()
        if not cart_items:
            cursor.close()
            conn.close()
            return render_template('cart.html', message="Your cart is empty.", name=session['username'])
        cursor.execute("SELECT * FROM signup WHERE id=%s", (session['id'],))
        user_info = cursor.fetchone()
        cursor.close()
        conn.close()
        return render_template('cart.html', cart_items=cart_items, user_info=user_info,
                       name=session['username'])
    except Error as e:
        return render_template('main.html', message=f"Database error: {e}")

@app.route('/removeFromCart/<int:column_id>', methods=['POST'])
def remove_from_cart(column_id):
        print(f"Removing item with columnId: {column_id}")
        if 'id' not in session:
            return {"success": False, "error": "Not signed in"}, 401
        try:
                conn = get_db_connection()
                cursor = conn.cursor()
                cursor.execute(
                    "DELETE FROM addToCart WHERE columnId=%s",
                    (column_id,)
                )
                conn.commit()
                cursor.close()
                conn.close()
                return {"success": True}, 200
        except Error as e:
                return {"success": False, "error": str(e)}, 500

@app.route("/addToCart", methods=['POST'])
def add_to_cart():
    if 'id' not in session:
        return redirect('/signin')
    product_id = request.form.get('product_id')

    product_name = str(request.form.get('product_name'))
    product_brand = str(request.form.get('product_brand'))
    product_review_count = str(request.form.get('product_review_count'))
    product_rating = str(request.form.get('product_rating'))
    product_price = str(request.form.get('product_price'))
    product_image = str(request.form.get('product_image'))
    print(f"Adding product to cart: {product_id}, {product_name}, {product_brand}, {product_review_count}, {product_rating}, {product_price}, {product_image}")
    if not product_id:
        return render_template('main.html', message="Product ID is required.")
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute(
            "INSERT INTO addToCart (id, prodId, prodName, prodBrand, prodReviewCount, prodRatings, prodPrice, prodImage) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
            (
            session['id'],
            product_id,
            product_name,
            product_brand,
            product_review_count,
            product_rating,
            product_price,
            product_image
            )
        )
        conn.commit()
        cursor.close()
        conn.close()
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('select count(*) from addToCart where id=%s', (session['id'],))
        count = cursor.fetchall()
        count = count[0][0] if count else 0
        print(count)
        return {"success": True, "cart_count":count }, 200
    except Error as e:
        return render_template('main.html', message=f"Database error: {e}")


if __name__=='__main__':
    app.run(debug=True, host='0.0.0.0', port=8000)
