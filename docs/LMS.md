# 图书管理系统（Library Management System）

## 一、项目背景与目标（Objective）

随着信息化的发展，传统图书馆在图书借阅、图书维护和用户管理等方面逐渐暴露出效率低、人工成本高、数据分散等问题。为了提升图书管理的规范性、可维护性和自动化水平，本项目设计并实现一个**基于 Web 的图书管理系统**。

本系统以真实图书馆业务为原型，覆盖用户管理、图书管理、借阅管理等核心功能，重点体现以下能力：

* 对真实业务需求的分析与抽象能力
* 关系型数据库的逻辑建模（LDM）与结构设计能力
* 基本 Web 应用的前后端协作能力

本系统在功能上追求**完整、清晰、可实现**，在实现上强调**低复杂度、易分工、易扩展**，作为小组协作开发与课程期末大作业的统一技术依据。

---

## 二、需求分析（Requirement Analysis）

### 2.1 系统角色与用户类型

系统中定义两类核心角色：

1. **普通用户（Reader）**

   * 注册并登录系统
   * 浏览和查询图书信息
   * 查看个人借阅记录

2. **管理员（Admin）**

   * 登录系统
   * 维护图书基础数据（增 / 删 / 改 / 查）
   * 管理作者、分类等基础信息
   * 查看全局借阅记录

系统通过“用户 + 角色”的方式进行权限区分，而不是为不同用户设计完全独立的系统。

---

### 2.2 功能性需求

#### 2.2.1 用户管理需求

* 用户注册（用户名、密码、邮箱）
* 用户登录与退出
* 用户与角色的关联管理（普通用户 / 管理员）

#### 2.2.2 图书管理需求（核心功能）

* 新增图书信息
* 修改图书信息
* 删除图书信息
* 查询图书信息（支持按书名、作者、分类等条件查询）

#### 2.2.3 作者与分类管理需求

* 一本图书可以对应多个作者
* 一个作者可以参与多本图书的编写
* 图书必须归属于某一分类

#### 2.2.4 借阅管理需求

* 记录用户借阅图书的行为
* 记录借阅时间、应还时间、归还时间
* 支持查询当前借阅与历史借阅记录

---

### 2.3 非功能性需求

* **可维护性**：模块划分清晰，数据库结构规范化
* **一致性**：业务规则在数据库与业务层保持一致
* **可扩展性**：后续可增加预约、统计报表等功能
* **安全性（说明层面）**：用户密码不以明文存储

---

## 三、系统总体设计（System Design）

本章在需求分析的基础上，对系统的整体架构、模块划分以及**前后端接口规范**进行详细设计。本设计既是系统实现的依据，也是小组成员协作开发时的统一接口文档。

---

### 3.1 系统架构设计

系统采用典型的 **B/S（Browser / Server）架构**，前后端分离设计：

* **前端（Client）**：

  * 技术：HTML + CSS + JavaScript + Bootstrap
  * 职责：页面展示、用户交互、向后端发送 HTTP 请求

* **后端（Server）**：

  * 技术：PHP（或 Python Flask）
  * 职责：业务逻辑处理、权限校验、数据库访问、统一返回数据

* **数据库（Database）**：

  * 技术：MySQL
  * 职责：数据持久化与一致性约束

前端通过 **HTTP/HTTPS** 与后端通信，数据格式统一使用 **JSON**。

---

### 3.2 接口设计总体约定（全局规范）

为保证系统接口风格统一、降低前后端协作成本，所有接口遵循以下约定。

#### 3.2.1 请求规范

* 请求方式：`GET / POST / PUT / DELETE`
* 请求路径：`/api/模块名/操作`
* 参数位置：

  * `GET`：参数通过 URL Query 传递
  * `POST / PUT`：参数通过 **JSON 请求体（Request Body）** 传递
  * 路径参数：通过 `{}` 标识，如 `/api/book/{book_id}`

---

#### 3.2.2 统一返回数据格式

后端所有接口返回统一 JSON 结构：

```json
{
  "code": 0,
  "message": "success",
  "data": {}
}
```

* `code`：状态码

  * `0`：成功
  * 非 0：失败
* `message`：结果说明
* `data`：返回的数据内容（对象或数组）

---

### 3.3 模块划分与详细接口设计

系统划分为以下核心模块，每个模块接口均可独立开发与测试。

---

### 3.3.1 用户管理模块（User Module）

**模块职责**：

* 用户注册、登录与身份信息查询

#### 接口 1：用户注册

* **URL**：`POST /api/user/register`
* **请求体（JSON）**：

```json
{
  "username": "string",
  "password": "string",
  "email": "string"
}
```

* **返回数据**：

```json
{
  "code": 0,
  "message": "register success",
  "data": null
}
```

---

#### 接口 2：用户登录

* **URL**：`POST /api/user/login`
* **请求体（JSON）**：

```json
{
  "username": "string",
  "password": "string"
}
```

* **返回数据**：

```json
{
  "code": 0,
  "message": "login success",
  "data": {
    "user_id": 1,
    "role": "ADMIN"
  }
}
```

---

### 3.3.2 图书管理模块（Book Module）

**模块职责**：

* 图书基础信息的增、删、改、查（核心 CRUD 模块）

#### 接口 1：新增图书

* **URL**：`POST /api/book/create`
* **请求体（JSON）**：

```json
{
  "title": "string",
  "isbn": "string",
  "category_id": 1,
  "publisher_id": 1
}
```

* **返回数据**：

```json
{
  "code": 0,
  "message": "book created successfully",
  "data": {
    "book_id": 1
  }
}
```

---

#### 接口 2：修改图书

* **URL**：`PUT /api/book/update/{book_id}`
* **请求体（JSON）**：

```json
{
  "title": "string",
  "category_id": 1
}
```

* **返回数据**：

```json
{
  "code": 0,
  "message": "book updated successfully",
  "data": null
}
```

---

#### 接口 3：删除图书

* **URL**：`DELETE /api/book/delete/{book_id}`

* **返回数据**：

```json
{
  "code": 0,
  "message": "book deleted successfully",
  "data": null
}
```

---

#### 接口 4：查询图书列表

* **URL**：`GET /api/book/list`
* **查询参数（Query）**：

  * `title`（可选）
  * `category_id`（可选）

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "book_id": 1,
      "title": "Database System",
      "isbn": "978-0123456789",
      "category_id": 1,
      "category_name": "Computer Science",
      "publisher_id": 1,
      "publisher_name": "Academic Press",
      "publish_date": "2024-01-01",
      "total_stock": 10,
      "available_stock": 8
    }
  ]
}
```

---

### 3.3.3 作者管理模块（Author Module）

**模块职责**：

* 作者信息维护

#### 接口 1：新增作者

* **URL**：`POST /api/author/create`
* **请求体（JSON）**：

```json
{
  "name": "string",
  "country": "string"
}
```

* **返回数据**：

```json
{
  "code": 0,
  "message": "author created successfully",
  "data": {
    "author_id": 1
  }
}
```

---

#### 接口 2：查询作者列表

* **URL**：`GET /api/author/list`
* **查询参数（Query）**：

  * `name`（可选）：作者姓名关键字

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "author_id": 1,
      "name": "Author A",
      "country": "Country A"
    }
  ]
}
```

---

### 3.3.4 分类管理模块（Category Module）

**模块职责**：

* 图书分类维护

#### 接口 1：新增分类

* **URL**：`POST /api/category/create`
* **请求体（JSON）**：

```json
{
  "category_name": "string",
  "description": "string"
}
```

* **返回数据**：

```json
{
  "code": 0,
  "message": "category created successfully",
  "data": {
    "category_id": 1
  }
}
```

---

#### 接口 2：查询分类列表

* **URL**：`GET /api/category/list`

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "category_id": 1,
      "category_name": "Computer Science",
      "description": "Books related to computer science"
    }
  ]
}
```

---

### 3.3.5 借阅管理模块（Borrow Module）

**模块职责**：

* 管理借阅记录

#### 接口 1：创建借阅记录

* **URL**：`POST /api/borrow/create`
* **请求体（JSON）**：

```json
{
  "user_id": 1,
  "book_id": 1,
  "due_date": "2025-01-01"
}
```

* **返回数据**：

```json
{
  "code": 0,
  "message": "borrow record created successfully",
  "data": {
    "borrow_id": 1
  }
}
```

---

#### 接口 2：归还图书

* **URL**：`PUT /api/borrow/return/{borrow_id}`

* **返回数据**：

```json
{
  "code": 0,
  "message": "book returned successfully",
  "data": null
}
```

---

#### 接口 3：查询用户借阅记录

* **URL**：`GET /api/borrow/user/{user_id}`

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "borrow_id": 1,
      "user_id": 1,
      "username": "Alice",
      "book_id": 1,
      "book_title": "Database System",
      "borrow_date": "2024-12-01T10:00:00Z",
      "due_date": "2025-01-01T10:00:00Z",
      "return_date": null,
      "status": "borrowed"
    }
  ]
}
```

---

### 3.3.6 数据查询与统计模块（Query & Analytics Module）

---

#### 模块职责

* 统一承载复杂 SQL 查询逻辑
* 对外提供查询接口，避免复杂 SQL 分散在业务模块中
* 支持教学与评估场景下对 SQL 能力的集中展示

---

### 接口 1：连接查询（Join Query：Inner / Outer）

* **URL**：`GET /api/query/book-authors`

* **请求参数（Query）**：

  * `book_id`（int，可选）

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "book_id": 1,
      "book_title": "Database System",
      "author_id": 1,
      "author_name": "Author A",
      "country": "Country A"
    }
  ]
}
```

---

### 接口 2：自连接查询（Self Join）

* **URL**：`GET /api/query/category-tree`

* **请求参数**：无

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "category_id": 1,
      "category_name": "Computer Science",
      "parent_category_id": null,
      "parent_category_name": null
    },
    {
      "category_id": 2,
      "category_name": "Database",
      "parent_category_id": 1,
      "parent_category_name": "Computer Science"
    }
  ]
}
```

---

### 接口 3：聚合查询（GROUP BY / ORDER BY）

* **URL**：`GET /api/query/book-count-by-category`

* **请求参数**：无

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "category_id": 1,
      "category_name": "Computer Science",
      "book_count": 10
    },
    {
      "category_id": 2,
      "category_name": "Mathematics",
      "book_count": 5
    }
  ]
}
```

---

### 接口 4：日期与时间函数查询

* **URL**：`GET /api/query/overdue-borrow`

* **请求参数**：无

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "borrow_id": 1,
      "user_id": 1,
      "username": "Alice",
      "book_id": 1,
      "book_title": "Database System",
      "borrow_date": "2024-11-01T10:00:00Z",
      "due_date": "2024-11-30T10:00:00Z",
      "days_overdue": 27
    }
  ]
}
```

---

### 接口 5：子查询（Subquery）

* **URL**：`GET /api/query/users-borrowed-book/{book_id}`

* **路径参数**：

  * `book_id`（int）：图书 ID

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "user_id": 1,
      "username": "Alice",
      "borrow_date": "2024-12-01T10:00:00Z"
    }
  ]
}
```

---

### 接口 6：相关子查询（Correlated Subquery）

* **URL**：`GET /api/query/most-active-users`

* **请求参数**：无

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "user_id": 2,
      "username": "Bob",
      "borrow_count": 12
    }
  ]
}
```

---

### 接口 7：集合运算（Set Operations）

* **URL**：`GET /api/query/active-or-admin-users`

* **请求参数**：无

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "user_id": 1,
      "username": "Alice",
      "role": "READER"
    },
    {
      "user_id": 3,
      "username": "Admin",
      "role": "ADMIN"
    }
  ]
}
```

---

### 接口 8：多表连接查询（Multi-table Join）

* **URL**：`GET /api/query/borrow-detail`

* **请求参数（Query）**：

  * `user_id`（int，可选）

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "borrow_id": 1,
      "user_id": 1,
      "username": "Alice",
      "book_id": 1,
      "book_title": "Database System",
      "author_names": "Author A, Author B",
      "category_name": "Computer Science",
      "borrow_date": "2024-12-01T10:00:00Z",
      "due_date": "2025-01-01T10:00:00Z",
      "return_date": null,
      "status": "borrowed"
    }
  ]
}
```

---

### 接口 9：除法查询（Division Query）

* **URL**：`GET /api/query/users-borrowed-all-categories`

* **请求参数**：无

* **返回数据**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "user_id": 5,
      "username": "Charlie"
    }
  ]
}
```

---

## 四、数据库逻辑数据模型（LDM，完整字段说明版）

所有实体均满足 **第三范式（3NF）**，字段类型以 MySQL 为目标数据库进行设计。

---

### 4.1 实体总览（用于画 LDM 图）

| 编号  | 实体名         | 说明           |
| --- | ----------- | ------------ |
| E1  | User        | 系统用户基本信息     |
| E2  | UserProfile | 用户扩展信息（1:1）  |
| E3  | Role        | 系统角色         |
| E4  | UserRole    | 用户-角色关联（M:N） |
| E5  | Book        | 图书基本信息       |
| E6  | Author      | 作者信息         |
| E7  | BookAuthor  | 图书-作者关联（M:N） |
| E8  | Category    | 图书分类         |
| E9  | Publisher   | 出版社信息        |
| E10 | Borrow      | 借阅记录         |


---

### 4.2 各实体字段详细说明（核心）

### 4.2.1 User（用户表）

**业务含义**：
系统中所有可登录用户的基础账号信息。

| 字段名        | 类型           | 主键 | 外键 | 说明           |
| ---------- | ------------ | -- | -- | ------------ |
| user_id    | INT          | ✔  |    | 用户唯一标识，自增    |
| username   | VARCHAR(50)  |    |    | 登录用户名，唯一     |
| password   | VARCHAR(255) |    |    | 加密后的密码       |
| email      | VARCHAR(100) |    |    | 邮箱           |
| status     | TINYINT      |    |    | 状态：1=启用，0=禁用 |
| created_at | DATETIME     |    |    | 注册时间         |

---

### 4.2.2 UserProfile（用户扩展信息表）

**业务含义**：
存储用户的个人属性信息，与 User 一对一关联。

| 字段名           | 类型          | 主键 | 外键 | 说明               |
| ------------- | ----------- | -- | -- | ---------------- |
| profile_id    | INT         | ✔  |    | 主键               |
| user_id       | INT         |    | ✔  | 关联 User(user_id) |
| real_name     | VARCHAR(50) |    |    | 真实姓名             |
| phone         | VARCHAR(20) |    |    | 联系电话             |
| register_date | DATE        |    |    | 注册日期             |

---

### 4.2.3 Role（角色表）

**业务含义**：
系统中定义的角色类型。

| 字段名         | 类型           | 主键 | 外键 | 说明                  |
| ----------- | ------------ | -- | -- | ------------------- |
| role_id     | INT          | ✔  |    | 角色ID                |
| role_name   | VARCHAR(50)  |    |    | 角色名（ADMIN / READER） |
| description | VARCHAR(100) |    |    | 角色描述                |

---

### 4.2.4 UserRole（用户-角色关联表）

**业务含义**：
实现 User 与 Role 的多对多关系。

| 字段名     | 类型  | 主键 | 外键 | 说明      |
| ------- | --- | -- | -- | ------- |
| id      | INT | ✔  |    | 主键      |
| user_id | INT |    | ✔  | 关联 User |
| role_id | INT |    | ✔  | 关联 Role |

---

### 4.2.5 Book（图书表）

**业务含义**：
系统中所有图书的基础信息。

| 字段名             | 类型           | 主键 | 外键 | 说明      |
| --------------- | ------------ | -- | -- | ------- |
| book_id         | INT          | ✔  |    | 图书ID    |
| isbn            | VARCHAR(20)  |    |    | ISBN 编号 |
| title           | VARCHAR(200) |    |    | 图书名称    |
| category_id     | INT          |    | ✔  | 所属分类    |
| publisher_id    | INT          |    | ✔  | 出版社     |
| publish_date    | DATE         |    |    | 出版日期    |
| total_stock     | INT          |    |    | 总库存     |
| available_stock | INT          |    |    | 可借库存    |

---

### 4.2.6 Author（作者表）

**业务含义**：
存储作者基本信息。

| 字段名       | 类型           | 主键 | 外键 | 说明   |
| --------- | ------------ | -- | -- | ---- |
| author_id | INT          | ✔  |    | 作者ID |
| name      | VARCHAR(100) |    |    | 作者姓名 |
| country   | VARCHAR(50)  |    |    | 国籍   |

---

### 4.2.7 BookAuthor（图书-作者关联表）

**业务含义**：
实现 Book 与 Author 的多对多关系。

| 字段名       | 类型  | 主键 | 外键 | 说明        |
| --------- | --- | -- | -- | --------- |
| id        | INT | ✔  |    | 主键        |
| book_id   | INT |    | ✔  | 关联 Book   |
| author_id | INT |    | ✔  | 关联 Author |

---

### 4.2.8 Category（分类表）

**业务含义**：
图书分类信息，用于查询与统计。

| 字段名           | 类型           | 主键 | 外键 | 说明   |
| ------------- | ------------ | -- | -- | ---- |
| category_id   | INT          | ✔  |    | 分类ID |
| category_name | VARCHAR(100) |    |    | 分类名称 |
| description   | VARCHAR(200) |    |    | 分类说明 |
| parent_id     | INT          |    | ✔  | 父分类ID，用于自连接查询 |

---

### 4.2.9 Publisher（出版社表）

**业务含义**：
图书出版社信息。

| 字段名          | 类型           | 主键 | 外键 | 说明    |
| ------------ | ------------ | -- | -- | ----- |
| publisher_id | INT          | ✔  |    | 出版社ID |
| name         | VARCHAR(100) |    |    | 出版社名称 |
| address      | VARCHAR(200) |    |    | 地址    |
| contact      | VARCHAR(50)  |    |    | 联系方式  |

---

### 4.2.10 Borrow（借阅记录表）

**业务含义**：
记录用户借阅图书的行为。

| 字段名         | 类型       | 主键 | 外键 | 说明                            |
| ----------- | -------- | -- | -- | ----------------------------- |
| borrow_id   | INT      | ✔  |    | 借阅记录ID                        |
| user_id     | INT      |    | ✔  | 借阅用户                          |
| book_id     | INT      |    | ✔  | 借阅图书                          |
| borrow_date | DATETIME |    |    | 借出时间                          |
| due_date    | DATETIME |    |    | 应还时间                          |
| return_date | DATETIME |    |    | 实际归还时间（可空）                    |
| status      | ENUM     |    |    | borrowed / returned / overdue |

---

### 4.3 实体关系汇总

* **一对一（1:1）**

  * User —— UserProfile
* **一对多（1:N）**

  * Category —— Book
  * Publisher —— Book
  * User —— Borrow
  * Book —— Borrow
* **多对多（M:N）**

  * User —— Role（通过 UserRole）
  * Book —— Author（通过 BookAuthor）

---

## 五、分工与实施建议

* **数据库设计人员**：依据第 4 章内容绘制 LDM / PDM，并生成 MySQL 脚本
* **后端开发人员**：依据第 3 章接口设计实现 API
* **前端开发人员**：依据模块接口完成页面与交互逻辑
* **文档与测试人员**：负责 SQL 查询整理与测试截图

---

## 六、总结

本文档作为图书管理系统项目的统一需求与设计依据，详细描述了系统功能、模块划分、接口设计以及数据库逻辑模型.
